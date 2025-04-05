// DOM Elements
const board = document.getElementById('kanban-board');
const addColumnBtn = document.getElementById('add-column-btn');
const themeToggle = document.getElementById('theme-toggle');
const modal = document.getElementById('card-modal');
const closeModal = document.querySelector('.close');
const saveCardBtn = document.getElementById('save-card');
const addSubtaskBtn = document.getElementById('add-subtask');
const subtasksContainer = document.getElementById('subtasks-container');
const timeEstimateInput = document.getElementById('time-estimate');
const reminderTypeSelect = document.getElementById('reminder-type');

// State Management
let currentCardId = null;
let currentColumnId = null;
let nextCardId = 1;
let isDragging = false;
let draggedCard = null;

// Load from localStorage if available
loadBoardState();

// Event Listeners
addColumnBtn.addEventListener('click', addNewColumn);
themeToggle.addEventListener('click', toggleTheme);
closeModal.addEventListener('click', closeCardModal);
saveCardBtn.addEventListener('click', saveCardChanges);
addSubtaskBtn.addEventListener('click', addSubtask);
timeEstimateInput.addEventListener('input', updateTimeVisualization);
document.addEventListener('DOMContentLoaded', setupInitialState);

// Delegate events for dynamically created elements
board.addEventListener('click', handleBoardClick);
board.addEventListener('dragstart', handleDragStart);
board.addEventListener('dragover', handleDragOver);
board.addEventListener('dragenter', handleDragEnter);
board.addEventListener('dragleave', handleDragLeave);
board.addEventListener('drop', handleDrop);
board.addEventListener('dragend', handleDragEnd);

// Setup initial state
function setupInitialState() {
    // If there are no columns, add sample data
    if (document.querySelectorAll('.column').length === 0) {
        addSampleData();
    }
    
    // Make column titles editable
    const columnTitles = document.querySelectorAll('.column-title');
    columnTitles.forEach(title => {
        title.addEventListener('blur', function() {
            saveBoardState();
        });
    });
    
    // Initialize card counters
    updateAllCardCounters();
    
    // Check theme preference
    const savedTheme = localStorage.getItem('kanbanTheme');
    if (savedTheme === 'dark') {
        document.body.setAttribute('data-theme', 'dark');
        themeToggle.checked = true;
    }
}

// Board Event Handlers
function handleBoardClick(e) {
    // Add new card button
    if (e.target.classList.contains('add-card-btn') || e.target.closest('.add-card-btn')) {
        const column = e.target.closest('.column');
        currentColumnId = column.dataset.id;
        openCardModal();
    }
    
    // Delete column button
    if (e.target.classList.contains('delete-column-btn') || e.target.closest('.delete-column-btn')) {
        const column = e.target.closest('.column');
        if (confirm('Are you sure you want to delete this column and all its cards?')) {
            column.remove();
            saveBoardState();
        }
    }
    
    // Edit card button
    if (e.target.classList.contains('edit-card') || e.target.closest('.edit-card')) {
        const card = e.target.closest('.card');
        const column = card.closest('.column');
        currentCardId = card.dataset.id;
        currentColumnId = column.dataset.id;
        openCardModal(card);
    }
    
    // Delete card button
    if (e.target.classList.contains('delete-card') || e.target.closest('.delete-card')) {
        const card = e.target.closest('.card');
        if (confirm('Are you sure you want to delete this card?')) {
            card.remove();
            updateCardCounter(card.closest('.column'));
            saveBoardState();
        }
    }
}

// Card Drag & Drop Handlers
function handleDragStart(e) {
    if (e.target.classList.contains('card')) {
        isDragging = true;
        draggedCard = e.target;
        e.dataTransfer.setData('text/plain', e.target.dataset.id);
        e.target.classList.add('dragging');
        
        // Delay to make sure the dragging class is added
        setTimeout(() => {
            e.target.style.opacity = '0.4';
        }, 0);
    }
}

function handleDragOver(e) {
    if (isDragging) {
        e.preventDefault();
    }
}

function handleDragEnter(e) {
    if (isDragging) {
        const column = e.target.closest('.column');
        const cardsContainer = e.target.closest('.cards-container');
        
        if (column) {
            column.classList.add('drag-over');
        }
        
        if (cardsContainer) {
            cardsContainer.classList.add('drag-over');
        }
    }
}

function handleDragLeave(e) {
    if (isDragging) {
        const column = e.target.closest('.column');
        const cardsContainer = e.target.closest('.cards-container');
        
        if (column) {
            column.classList.remove('drag-over');
        }
        
        if (cardsContainer) {
            cardsContainer.classList.remove('drag-over');
        }
    }
}

function handleDrop(e) {
    e.preventDefault();
    
    if (isDragging) {
        const column = e.target.closest('.column');
        
        if (column) {
            column.classList.remove('drag-over');
            const cardsContainer = column.querySelector('.cards-container');
            const cardId = e.dataTransfer.getData('text/plain');
            const card = document.querySelector(`.card[data-id="${cardId}"]`);
            
            // Handle dropping into a different column
            if (card && card.closest('.column') !== column) {
                const oldColumn = card.closest('.column');
                cardsContainer.appendChild(card);
                
                // Update card counters for both columns
                updateCardCounter(oldColumn);
                updateCardCounter(column);
                saveBoardState();
            }
        }
    }
}

function handleDragEnd(e) {
    if (isDragging) {
        isDragging = false;
        draggedCard = null;
        
        // Remove dragging styles
        e.target.classList.remove('dragging');
        e.target.style.opacity = '1';
        
        // Remove drag-over classes from all elements
        document.querySelectorAll('.drag-over').forEach(el => {
            el.classList.remove('drag-over');
        });
    }
}

// Column Functions
function addNewColumn() {
    const columnId = 'column-' + Date.now();
    const columnTemplate = `
        <div class="column" data-id="${columnId}">
            <div class="column-header">
                <h2 class="column-title" contenteditable="true">New Column</h2>
                <div class="column-controls">
                    <span class="card-count">0</span>
                    <button class="add-card-btn"><i class="fas fa-plus"></i></button>
                    <button class="delete-column-btn"><i class="fas fa-trash"></i></button>
                </div>
            </div>
            <div class="cards-container"></div>
        </div>
    `;
    
    board.insertAdjacentHTML('beforeend', columnTemplate);
    
    // Make the new column title editable
    const newColumn = board.lastElementChild;
    const newColumnTitle = newColumn.querySelector('.column-title');
    newColumnTitle.addEventListener('blur', saveBoardState);
    
    // Save the updated board state
    saveBoardState();
}

// Card Functions
function createCard(data, columnId) {
    const cardId = data.id || `card-${nextCardId++}`;
    const column = document.querySelector(`.column[data-id="${columnId}"]`);
    
    if (!column) return;
    
    const cardsContainer = column.querySelector('.cards-container');
    const dueDateString = data.dueDate ? new Date(data.dueDate).toLocaleDateString() : '';
    
    const cardTemplate = `
        <div class="card" draggable="true" data-id="${cardId}">
            ${data.reminderType ? `<div class="reminder-badge"><i class="fas fa-bell"></i> ${data.reminderType}</div>` : ''}
            <div class="card-actions">
                <button class="edit-card"><i class="fas fa-edit"></i></button>
                <button class="delete-card"><i class="fas fa-trash"></i></button>
            </div>
            <h3 class="card-title">${data.title}</h3>
            <p class="card-description">${data.description || ''}</p>
            ${data.subtasks ? `
                <div class="card-progress">
                    <div class="progress-bar" style="width: ${calculateProgress(data.subtasks)}%"></div>
                </div>
            ` : ''}
            <div class="card-meta">
                ${data.timeEstimate ? `
                <div class="time-estimate">
                    <i class="fas fa-clock"></i>
                    <span>${formatTime(data.timeEstimate)}</span>
                </div>` : ''}
                ${dueDateString ? `
                <div class="card-due-date">
                    <i class="fas fa-calendar"></i>
                    <span>${dueDateString}</span>
                </div>` : ''}
                <div class="card-priority priority-${data.priority || 'low'}">${capitalizeFirstLetter(data.priority || 'low')}</div>
            </div>
        </div>
    `;
    
    cardsContainer.insertAdjacentHTML('beforeend', cardTemplate);
    updateCardCounter(column);
}

// Modal Functions
function openCardModal(card = null) {
    const titleInput = document.getElementById('card-title');
    const descriptionInput = document.getElementById('card-description');
    const prioritySelect = document.getElementById('card-priority');
    const dueDateInput = document.getElementById('card-due-date');
    
    // Clear previous values
    titleInput.value = '';
    descriptionInput.value = '';
    prioritySelect.value = 'low';
    dueDateInput.value = '';
    
    // If editing an existing card, fill in its values
    if (card) {
        titleInput.value = card.querySelector('.card-title').textContent;
        const description = card.querySelector('.card-description');
        if (description) {
            descriptionInput.value = description.textContent;
        }
        
        const priorityEl = card.querySelector('.card-priority');
        if (priorityEl) {
            const priorityClass = Array.from(priorityEl.classList)
                .find(cls => cls.startsWith('priority-'));
            if (priorityClass) {
                prioritySelect.value = priorityClass.replace('priority-', '');
            }
        }
        
        const dueDateEl = card.querySelector('.card-due-date span');
        if (dueDateEl) {
            // Convert date string to YYYY-MM-DD format
            const dateParts = dueDateEl.textContent.split('/');
            if (dateParts.length === 3) {
                const month = dateParts[0].padStart(2, '0');
                const day = dateParts[1].padStart(2, '0');
                const year = dateParts[2];
                dueDateInput.value = `${year}-${month}-${day}`;
            }
        }
    }
    
    modal.style.display = 'block';
}

function closeCardModal() {
    modal.style.display = 'none';
    currentCardId = null;
}

function saveCardChanges() {
    const titleInput = document.getElementById('card-title');
    const descriptionInput = document.getElementById('card-description');
    const prioritySelect = document.getElementById('card-priority');
    const dueDateInput = document.getElementById('card-due-date');
    
    // Validate title
    if (!titleInput.value.trim()) {
        alert('Please enter a title for the card');
        return;
    }
    
    const cardData = {
        id: currentCardId || `card-${nextCardId++}`,
        title: titleInput.value.trim(),
        description: descriptionInput.value.trim(),
        priority: prioritySelect.value,
        dueDate: dueDateInput.value,
        timeEstimate: parseInt(timeEstimateInput.value) || 0,
        reminderType: reminderTypeSelect.value !== 'none' ? reminderTypeSelect.value : null,
        subtasks: Array.from(subtasksContainer.querySelectorAll('.subtask-item')).map(item => ({
            text: item.querySelector('input[type="text"]').value,
            completed: item.querySelector('input[type="checkbox"]').checked
        }))
    };
    
    // If editing an existing card
    if (currentCardId) {
        const card = document.querySelector(`.card[data-id="${currentCardId}"]`);
        if (card) {
            card.querySelector('.card-title').textContent = cardData.title;
            card.querySelector('.card-description').textContent = cardData.description;
            
            const priorityEl = card.querySelector('.card-priority');
            priorityEl.className = `card-priority priority-${cardData.priority}`;
            priorityEl.textContent = capitalizeFirstLetter(cardData.priority);
            
            const dueDateContainer = card.querySelector('.card-due-date');
            if (cardData.dueDate) {
                const dueDateString = new Date(cardData.dueDate).toLocaleDateString();
                if (dueDateContainer) {
                    dueDateContainer.querySelector('span').textContent = dueDateString;
                } else {
                    const metaDiv = card.querySelector('.card-meta');
                    const dueDateHTML = `
                        <div class="card-due-date">
                            <i class="fas fa-calendar"></i>
                            <span>${dueDateString}</span>
                        </div>
                    `;
                    metaDiv.insertAdjacentHTML('afterbegin', dueDateHTML);
                }
            } else if (dueDateContainer) {
                dueDateContainer.remove();
            }
        }
    } else {
        // Creating a new card
        createCard(cardData, currentColumnId);
    }
    
    // Save board state and close modal
    saveBoardState();
    closeCardModal();
}

// Utility Functions
function updateCardCounter(column) {
    const cardsContainer = column.querySelector('.cards-container');
    const counter = column.querySelector('.card-count');
    const cardCount = cardsContainer.querySelectorAll('.card').length;
    counter.textContent = cardCount;
}

function updateAllCardCounters() {
    const columns = document.querySelectorAll('.column');
    columns.forEach(updateCardCounter);
}

function toggleTheme() {
    if (themeToggle.checked) {
        document.body.setAttribute('data-theme', 'dark');
        localStorage.setItem('kanbanTheme', 'dark');
    } else {
        document.body.removeAttribute('data-theme');
        localStorage.setItem('kanbanTheme', 'light');
    }
}

function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

// Persistence Functions
function saveBoardState() {
    const columns = Array.from(document.querySelectorAll('.column')).map(column => {
        const cards = Array.from(column.querySelectorAll('.card')).map(card => {
            const title = card.querySelector('.card-title').textContent;
            const description = card.querySelector('.card-description')?.textContent || '';
            
            const priorityEl = card.querySelector('.card-priority');
            const priorityClass = Array.from(priorityEl.classList)
                .find(cls => cls.startsWith('priority-'));
            const priority = priorityClass ? priorityClass.replace('priority-', '') : 'low';
            
            const dueDateEl = card.querySelector('.card-due-date span');
            const dueDate = dueDateEl ? dueDateEl.textContent : '';
            
            return {
                id: card.dataset.id,
                title,
                description,
                priority,
                dueDate
            };
        });
        
        return {
            id: column.dataset.id,
            title: column.querySelector('.column-title').textContent,
            cards
        };
    });
    
    localStorage.setItem('kanbanBoardState', JSON.stringify({
        columns,
        nextCardId
    }));
}

function loadBoardState() {
    const savedState = localStorage.getItem('kanbanBoardState');
    if (!savedState) return;
    
    try {
        const state = JSON.parse(savedState);
        
        // Clear existing columns
        board.innerHTML = '';
        
        // Restore card ID counter
        nextCardId = state.nextCardId || 1;
        
        // Rebuild columns and cards
        state.columns.forEach(colData => {
            const columnTemplate = `
                <div class="column" data-id="${colData.id}">
                    <div class="column-header">
                        <h2 class="column-title" contenteditable="true">${colData.title}</h2>
                        <div class="column-controls">
                            <span class="card-count">0</span>
                            <button class="add-card-btn"><i class="fas fa-plus"></i></button>
                            <button class="delete-column-btn"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                    <div class="cards-container"></div>
                </div>
            `;
            
            board.insertAdjacentHTML('beforeend', columnTemplate);
            
            // Add cards to this column
            colData.cards.forEach(cardData => {
                createCard(cardData, colData.id);
            });
        });
    } catch (error) {
        console.error('Error loading board state:', error);
    }
}

// Sample data for initial state
function addSampleData() {
    const sampleData = [
        {
            id: 'todo',
            title: 'To Do',
            cards: [
                {
                    id: 'card-1',
                    title: 'Research competitors',
                    description: 'Look at similar products and identify key features',
                    priority: 'medium',
                    dueDate: '2025-03-20'
                },
                {
                    id: 'card-2',
                    title: 'Create wireframes',
                    description: 'Design initial layouts for mobile and desktop',
                    priority: 'high',
                    dueDate: '2025-03-25'
                }
            ]
        },
        {
            id: 'progress',
            title: 'In Progress',
            cards: [
                {
                    id: 'card-3',
                    title: 'User interviews',
                    description: 'Conduct 5 user interviews to validate design concepts',
                    priority: 'high',
                    dueDate: '2025-03-15'
                }
            ]
        },
        {
            id: 'done',
            title: 'Done',
            cards: [
                {
                    id: 'card-4',
                    title: 'Project kickoff',
                    description: 'Initial team meeting and project setup',
                    priority: 'low',
                    dueDate: '2025-03-10'
                }
            ]
        }
    ];
    
    // Clear existing columns
    board.innerHTML = '';
    
    // Create columns and cards from sample data
    sampleData.forEach(colData => {
        const columnTemplate = `
            <div class="column" data-id="${colData.id}">
                <div class="column-header">
                    <h2 class="column-title" contenteditable="true">${colData.title}</h2>
                    <div class="column-controls">
                        <span class="card-count">0</span>
                        <button class="add-card-btn"><i class="fas fa-plus"></i></button>
                        <button class="delete-column-btn"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
                <div class="cards-container"></div>
            </div>
        `;
        
        board.insertAdjacentHTML('beforeend', columnTemplate);
        
        // Add cards to this column
        colData.cards.forEach(cardData => {
            createCard(cardData, colData.id);
        });
    });
    
    // Update next card ID
    nextCardId = 5;
}

// New functions for executive function features
function addSubtask() {
    const subtaskItem = document.createElement('div');
    subtaskItem.className = 'subtask-item';
    subtaskItem.innerHTML = `
        <input type="checkbox">
        <input type="text" placeholder="Enter subtask">
        <button onclick="removeSubtask(this)"><i class="fas fa-times"></i></button>
    `;
    subtasksContainer.appendChild(subtaskItem);
}

function removeSubtask(button) {
    button.closest('.subtask-item').remove();
    updateCardProgress();
}

function updateTimeVisualization() {
    const minutes = parseInt(timeEstimateInput.value) || 0;
    const visualization = document.querySelector('.time-visualization');
    visualization.innerHTML = `<div class="time-block" style="width: ${Math.min(minutes/60 * 100, 100)}%"></div>`;
}

function updateCardProgress() {
    const subtasks = document.querySelectorAll('.subtask-item');
    const completed = Array.from(subtasks).filter(item => item.querySelector('input[type="checkbox"]').checked).length;
    const total = subtasks.length;
    const progress = total ? (completed / total * 100) : 0;
    
    const progressBar = document.querySelector('.progress-bar');
    if (progressBar) {
        progressBar.style.width = `${progress}%`;
    }
}

// Helper functions
function calculateProgress(subtasks) {
    if (!subtasks || !subtasks.length) return 0;
    const completed = subtasks.filter(task => task.completed).length;
    return (completed / subtasks.length) * 100;
}

function formatTime(minutes) {
    if (minutes < 60) return `${minutes}m`;
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return mins ? `${hours}h ${mins}m` : `${hours}h`;
}