
function switchForm(formType) {
    const loginTab = document.getElementById('loginTab');
    const registerTab = document.getElementById('registerTab');
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');

    if (formType === 'login') {
        loginTab.classList.add('text-blue-600', 'border-blue-600');
        loginTab.classList.remove('text-gray-500', 'border-transparent');
        registerTab.classList.add('text-gray-500', 'border-transparent');
        registerTab.classList.remove('text-blue-600', 'border-blue-600');
        loginForm.classList.remove('hidden');
        registerForm.classList.add('hidden');
    } else {
        registerTab.classList.add('text-blue-600', 'border-blue-600');
        registerTab.classList.remove('text-gray-500', 'border-transparent');
        loginTab.classList.add('text-gray-500', 'border-transparent');
        loginTab.classList.remove('text-blue-600', 'border-blue-600');
        registerForm.classList.remove('hidden');
        loginForm.classList.add('hidden');
    }
}

// Form submission handling
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        // Add your form submission logic here
        console.log('Form submitted');
    });
});
