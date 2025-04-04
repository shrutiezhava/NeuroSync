import streamlit as st
import pytube
from pytube import YouTube
import speech_recognition as sr
import os
import moviepy.editor as mp

def download_video(url, output_path="temp_downloads"):
    """Download a YouTube video given its URL"""
    try:
        # Create output directory if it doesn't exist
        if not os.path.exists(output_path):
            os.makedirs(output_path)
            
        # Download the video
        yt = YouTube(url)
        video = yt.streams.filter(progressive=True, file_extension='mp4').order_by('resolution').desc().first()
        
        # Get video title for filename
        title = yt.title
        safe_title = "".join([c for c in title if c.isalpha() or c.isdigit() or c==' ']).rstrip()
        
        video_path = video.download(output_path=output_path, filename=f"{safe_title}.mp4")
        
        st.success(f"Downloaded: {yt.title}")
        return video_path, yt.title
        
    except Exception as e:
        st.error(f"Error downloading video: {str(e)}")
        return None, None

def convert_video_to_audio(video_path, output_path="temp_audio"):
    """Convert a video file to audio (WAV format)"""
    try:
        # Create output directory if it doesn't exist
        if not os.path.exists(output_path):
            os.makedirs(output_path)
            
        # Get base filename without extension
        video_filename = os.path.basename(video_path)
        audio_filename = os.path.splitext(video_filename)[0] + ".wav"
        audio_path = os.path.join(output_path, audio_filename)
        
        # Convert video to audio
        video_clip = mp.VideoFileClip(video_path)
        audio_clip = video_clip.audio
        audio_clip.write_audiofile(audio_path)
        
        # Close clips to release resources
        audio_clip.close()
        video_clip.close()
        
        return audio_path
        
    except Exception as e:
        st.error(f"Error converting video to audio: {str(e)}")
        return None

def transcribe_audio(audio_path):
    """Transcribe audio to text using speech recognition"""
    try:
        recognizer = sr.Recognizer()
        
        # Load audio file
        with sr.AudioFile(audio_path) as source:
            st.info("Processing audio... This may take a while depending on the length of the video.")
            audio_data = recognizer.record(source)
            
            # Transcribe audio
            st.info("Transcribing audio to text...")
            text = recognizer.recognize_google(audio_data)
            return text
    
    except Exception as e:
        st.error(f"Error transcribing audio: {str(e)}")
        return None

def cleanup(file_path):
    """Remove temporary files"""
    try:
        if os.path.exists(file_path):
            os.remove(file_path)
    except Exception as e:
        st.warning(f"Could not remove temporary file: {str(e)}")

def main():
    st.title("YouTube Video Transcriber")
    st.write("Convert YouTube videos to text format")
    
    # URL input
    youtube_url = st.text_input("Enter YouTube Video URL")
    
    if st.button("Transcribe Video"):
        if youtube_url:
            with st.spinner("Working on it..."):
                # Step 1: Download the video
                st.subheader("Step 1: Downloading video")
                video_path, video_title = download_video(youtube_url)
                
                if video_path:
                    # Step 2: Convert video to audio
                    st.subheader("Step 2: Converting video to audio")
                    audio_path = convert_video_to_audio(video_path)
                    
                    if audio_path:
                        # Step 3: Transcribe audio to text
                        st.subheader("Step 3: Transcribing audio to text")
                        transcript = transcribe_audio(audio_path)
                        
                        if transcript:
                            # Display results
                            st.subheader("Transcription Results")
                            st.write(f"Video Title: {video_title}")
                            
                            # Display transcript in a text area
                            st.text_area("Transcript", transcript, height=300)
                            
                            # Download option for the transcript
                            st.download_button(
                                label="Download Transcript as Text File",
                                data=transcript,
                                file_name=f"{video_title}_transcript.txt",
                                mime="text/plain"
                            )
                            
                        # Clean up temporary files
                        cleanup(video_path)
                        cleanup(audio_path)
        else:
            st.warning("Please enter a YouTube URL")

if __name__ == "__main__":
    main()
