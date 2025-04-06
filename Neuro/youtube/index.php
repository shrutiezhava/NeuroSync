<?php
$pythonPort = 5000;
$connection = @fsockopen("127.0.0.1", $pythonPort);

if (!$connection) {
    // Flask is NOT running, so we start it in background
    pclose(popen("start /B python app.py", "r"));
} else {
    fclose($connection); // Flask already running
}
?>

<?php
// index.php - PHP frontend for YouTube transcript extraction and summarization

// Initialize variables
$videoUrl = "";
$transcript = "";
$summary = "";
$error = "";
$videoId = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['url'])) {
    $videoUrl = $_POST['url'];
    
    // Call the Python API
    $apiUrl = "http://localhost:5000/api/process";
    $data = array('url' => $videoUrl);
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data)
        )
    );
    
    $context = stream_context_create($options);
    
    try {
        $result = file_get_contents($apiUrl, false, $context);
        
        if ($result === FALSE) {
            $error = "Failed to connect to the Python backend service.";
        } else {
            $response = json_decode($result, true);
            
            if (isset($response['error'])) {
                $error = "API Error: " . $response['error'];
            } else {
                $videoId = $response['video_id'];
                $transcript = $response['transcript'];
                $summary = $response['summary'];
            }
        }
    } catch (Exception $e) {
        $error = "An error occurred: " . $e->getMessage();
    }
}

// Function to extract YouTube video ID (as fallback)
function getYoutubeId($url) {
    $pattern = '/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
    preg_match($pattern, $url, $matches);
    return isset($matches[1]) ? $matches[1] : false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YouTube Video Transcriber & Summarizer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 2rem;
            background-color: #f8f9fa;
        }
        
        .main-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        
        .video-container {
            position: relative;
            padding-bottom: 56.25%;
            margin-bottom: 20px;
        }
        
        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 5px;
        }
        
        .summary-container {
            background-color: #f0f7ff;
            border-left: 4px solid #0d6efd;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .transcript-container {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            padding: 15px;
            border-radius: 5px;
            background-color: #f8f9fa;
        }

        .loading {
            text-align: center;
            padding: 20px;
            display: none;
        }
        
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-container">
            <h1 class="text-center mb-4">YouTube Video Transcriber & Summarizer</h1>
            
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="mb-4" id="videoForm">
                <div class="input-group mb-3">
                    <input type="text" name="url" class="form-control" placeholder="Enter YouTube video URL" value="<?php echo htmlspecialchars($videoUrl); ?>" required>
                    <button class="btn btn-primary" type="submit">Process Video</button>
                </div>
            </form>
            
            <div class="loading" id="loadingIndicator">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Processing video... This may take a minute.</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($videoUrl && !$error): ?>
                <div class="video-container mb-4">
                    <?php 
                    $embedId = $videoId ? $videoId : getYoutubeId($videoUrl);
                    if ($embedId):
                    ?>
                    <iframe src="https://www.youtube.com/embed/<?php echo $embedId; ?>" frameborder="0" allowfullscreen></iframe>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($summary): ?>
                <h3>Summary</h3>
                <div class="summary-container">
                    <?php echo nl2br(htmlspecialchars($summary)); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($transcript): ?>
                <h3>Transcript</h3>
                <div class="transcript-container">
                    <?php echo nl2br(htmlspecialchars($transcript)); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('videoForm').addEventListener('submit', function() {
            document.getElementById('loadingIndicator').style.display = 'block';
        });
    </script>
</body>
</html>