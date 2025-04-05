<?php
// YouTube Video Summarizer with Gemini API
// This version doesn't require local downloads for the user

// Configuration
$config = [
    'temp_dir' => 'temp', // Server-side temporary directory
    'gemini_api_key' => '', // Replace with your actual Gemini API key
    'max_summary_length' => 1500, // Maximum length for summary
];

// Create temp directory if it doesn't exist
if (!file_exists($config['temp_dir'])) {
    mkdir($config['temp_dir'], 0755, true);
}

/**
 * Extract YouTube video ID from URL
 */
function extract_youtube_id($url) {
    $pattern = '/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
    preg_match($pattern, $url, $matches);
    return isset($matches[1]) ? $matches[1] : false;
}

/**
 * Get YouTube video metadata (title, description)
 */
function get_video_metadata($video_id) {
    $url = "https://www.youtube.com/watch?v={$video_id}";
    $html = file_get_contents($url);
    
    // Extract title
    preg_match('/<title>(.*?)<\/title>/', $html, $title_matches);
    $title = isset($title_matches[1]) ? str_replace(' - YouTube', '', $title_matches[1]) : 'Unknown Title';
    
    // Get description (simplified)
    preg_match('/"description":"(.*?)"/', $html, $desc_matches);
    $description = isset($desc_matches[1]) ? $desc_matches[1] : '';
    $description = str_replace('\n', ' ', $description);
    
    return [$title, $description];
}

/**
 * Get YouTube transcript via API
 */
function get_youtube_transcript($video_id) {
    // This is a server-side request to a transcript API service
    // Using a third-party service that provides transcripts
    $url = "https://youtubetranscript.com/api/transcript/{$video_id}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    if (!$response) {
        // If the API fails, inform the user
        return null;
    }
    
    $data = json_decode($response, true);
    
    // Format varies by API - this is a common structure
    if (isset($data['transcript'])) {
        return $data['transcript'];
    } elseif (isset($data['text'])) {
        return $data['text'];
    }
    
    // If structured differently, try to extract transcript from segments
    if (isset($data['segments'])) {
        $transcript = '';
        foreach ($data['segments'] as $segment) {
            if (isset($segment['text'])) {
                $transcript .= $segment['text'] . ' ';
            }
        }
        return trim($transcript);
    }
    
    return null;
}

/**
 * Generate summary using Gemini API 2.0
 */
function generate_summary($transcript, $video_title, $api_key) {
    // Prepare text for summarization
    $prompt = "You are an expert at summarizing video content. Create a concise summary of the following transcript from a YouTube video titled \"$video_title\". Format your response as follows: 1) First, provide a one-paragraph overview of what the video is about. 2) Then, provide 5-7 bullet points of the most important information from the video. Focus on key concepts, insights, and takeaways that would be most valuable to someone who hasn't watched the video.\n\nTranscript:\n$transcript";
    
    // Call Gemini API
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://generativelanguage.googleapis.com/v2/models/gemini-pro:generateContent',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.2,
                'maxOutputTokens' => 800,
                'topP' => 0.8,
                'topK' => 40
            ]
        ]),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'x-goog-api-key: ' . $api_key
        ]
    ]);
    
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    
    if ($err) {
        error_log("cURL Error: " . $err);
        return "Failed to generate summary: $err";
    }
    
    $response_data = json_decode($response, true);
    
    // Extract the summary text from Gemini's response
    if (isset($response_data['candidates'][0]['content']['parts'][0]['text'])) {
        return $response_data['candidates'][0]['content']['parts'][0]['text'];
    }
    
    return "Failed to generate summary: Invalid API response";
}

/**
 * Main function to process a YouTube URL
 */
function process_youtube_url($youtube_url, $config) {
    // Extract video ID
    $video_id = extract_youtube_id($youtube_url);
    if (!$video_id) {
        return "Invalid YouTube URL. Please provide a valid YouTube video link.";
    }
    
    // Get video metadata
    list($video_title, $video_description) = get_video_metadata($video_id);
    
    // Get transcript directly from YouTube (no download needed)
    $transcript = get_youtube_transcript($video_id);
    
    if (!$transcript) {
        return "Failed to retrieve transcript for this video. The video may not have captions available.";
    }
    
    // Generate summary with Gemini
    $summary = generate_summary($transcript, $video_title, $config['gemini_api_key']);
    
    // Return results
    $result = [
        'video_id' => $video_id,
        'title' => $video_title,
        'summary' => $summary,
        'transcript' => $transcript
    ];
    
    return $result;
}

// Handle AJAX request
if (isset($_POST['action']) && $_POST['action'] === 'process_video') {
    header('Content-Type: application/json');
    
    if (empty($_POST['youtube_url'])) {
        echo json_encode(['error' => 'Please enter a YouTube URL']);
        exit;
    }
    
    $youtube_url = $_POST['youtube_url'];
    $result = process_youtube_url($youtube_url, $config);
    
    if (is_array($result)) {
        echo json_encode($result);
    } else {
        echo json_encode(['error' => $result]);
    }
    
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YouTube Video Summarizer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 2rem;
        }
        .header {
            margin-bottom: 2rem;
            text-align: center;
        }
        .header h1 {
            color: #FF0000;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        .video-container {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            max-width: 100%;
            border-radius: 10px 10px 0 0;
        }
        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }
        .nav-pills .nav-link.active {
            background-color: #FF0000;
        }
        .btn-primary {
            background-color: #FF0000;
            border-color: #FF0000;
        }
        .btn-primary:hover {
            background-color: #cc0000;
            border-color: #cc0000;
        }
        .spinner-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 200px;
        }
        #summary-content, #transcript-content {
            white-space: pre-line;
        }
        .key-points {
            padding-left: 20px;
        }
        .key-points li {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>YouTube Video Summarizer</h1>
            <p class="lead">Get instant transcripts and AI-powered summaries of any YouTube video</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form id="youtube-form">
                            <div class="mb-3">
                                <label for="youtube_url" class="form-label">Enter YouTube Video URL:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="youtube_url" name="youtube_url" placeholder="https://www.youtube.com/watch?v=..." required>
                                    <button type="submit" class="btn btn-primary">Generate Summary</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div id="loading" class="spinner-container d-none">
                    <div class="spinner-border text-danger" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="ms-2">Analyzing video content...</span>
                </div>
                
                <div id="results" class="d-none">
                    <div class="card mb-4">
                        <div id="video-embed" class="video-container">
                            <!-- Video will be embedded here -->
                        </div>
                        <div class="card-body">
                            <h2 id="video-title" class="card-title"></h2>
                        </div>
                    </div>
                    
                    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pills-summary-tab" data-bs-toggle="pill" data-bs-target="#pills-summary" type="button" role="tab">Summary</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-transcript-tab" data-bs-toggle="pill" data-bs-target="#pills-transcript" type="button" role="tab">Full Transcript</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="pills-summary" role="tabpanel">
                            <div class="card">
                                <div class="card-body">
                                    <h3>Video Summary</h3>
                                    <div id="summary-content"></div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="pills-transcript" role="tabpanel">
                            <div class="card">
                                <div class="card-body">
                                    <div id="transcript-content"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('youtube-form');
            const loadingDiv = document.getElementById('loading');
            const resultsDiv = document.getElementById('results');
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const youtubeUrl = document.getElementById('youtube_url').value;
                if (!youtubeUrl) return;
                
                // Show loading, hide results
                loadingDiv.classList.remove('d-none');
                resultsDiv.classList.add('d-none');
                
                // Send AJAX request
                const formData = new FormData();
                formData.append('action', 'process_video');
                formData.append('youtube_url', youtubeUrl);
                
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    // Hide loading
                    loadingDiv.classList.add('d-none');
                    
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    
                    // Show results
                    resultsDiv.classList.remove('d-none');
                    
                    // Set video title
                    document.getElementById('video-title').textContent = data.title;
                    
                    // Embed video
                    const videoEmbed = document.getElementById('video-embed');
                    videoEmbed.innerHTML = `<iframe src="https://www.youtube.com/embed/${data.video_id}" allowfullscreen></iframe>`;
                    
                    // Set summary content (format bullet points)
                    let summary = data.summary;
                    document.getElementById('summary-content').innerHTML = summary;
                    
                    // Set transcript content
                    document.getElementById('transcript-content').textContent = data.transcript;
                })
                .catch(error => {
                    loadingDiv.classList.add('d-none');
                    alert('Error processing request: ' + error.message);
                });
            });
        });
    </script>
</body>
</html>