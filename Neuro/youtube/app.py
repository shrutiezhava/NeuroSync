# app.py - Flask backend for YouTube transcript extraction
from flask import Flask, request, jsonify
from youtube_transcript_api import YouTubeTranscriptApi
import google.generativeai as genai
import re
import os
from dotenv import load_dotenv

# Load environment variables
load_dotenv()

# Configure Google Gemini API
genai.configure(api_key=os.getenv('GEMINI_API_KEY'))

app = Flask(__name__)

def get_video_id(url):
    """Extract YouTube video ID from URL"""
    pattern = r'(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})'
    match = re.search(pattern, url)
    return match.group(1) if match else None

def get_transcript(video_id):
    """Get transcript using youtube_transcript_api"""
    try:
        transcript_list = YouTubeTranscriptApi.get_transcript(video_id)
        transcript_text = ' '.join([item['text'] for item in transcript_list])
        return transcript_text
    except Exception as e:
        return str(e)

def summarize_with_gemini(text):
    """Summarize text using Google's Gemini API"""
    try:
        model = genai.GenerativeModel('gemini-2.0-flash')
        prompt = f"Please provide a concise summary of the following video transcript:\n\n{text}"
        
        response = model.generate_content(prompt)
        return response.text
    except Exception as e:
        return str(e)

@app.route('/api/process', methods=['POST'])
def process_video():
    """Process a YouTube video: extract transcript and summarize"""
    data = request.json
    url = data.get('url')
    
    if not url:
        return jsonify({'error': 'No URL provided'}), 400
    
    video_id = get_video_id(url)
    if not video_id:
        return jsonify({'error': 'Invalid YouTube URL'}), 400
    
    transcript = get_transcript(video_id)
    if transcript.startswith('Exception'):
        return jsonify({'error': transcript}), 500
    
    summary = summarize_with_gemini(transcript)
    
    return jsonify({
        'video_id': video_id,
        'transcript': transcript,
        'summary': summary
    })

if __name__ == '__main__':
    app.run(debug=True, port=5000)