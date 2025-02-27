import React, { useState } from "react";

type props = { videoUrl: string }
const VideoPreview: React.FC<props> = ({ videoUrl }) => {
  const [downloading, setDownloading] = useState(false);
  const [downloaded, setDownloaded] = useState(false);

  const downloadVideo = async () => {
    try {
      setDownloading(true);

      // Download video file
      await fetch(videoUrl);

      setDownloaded(true);
    } catch (error) {
      console.error("Video download failed:", error);
    } finally {
      setDownloading(false);
    }
  };

  const checkVideoInCache = async (url: string): Promise<void> => {
    try {
      const response = await fetch(url, {
        method: 'GET',
        cache: 'only-if-cached',
        mode: 'same-origin'
      });

      if (response && response.ok) {
        setDownloaded(true);
      }
    } catch (error) {
    }
  }

  checkVideoInCache(videoUrl);

  return (
    <div className="relative bg-black h-60 flex items-center justify-center rounded-b rounded-t w-full">
      {downloaded ? (
        <video controls className="w-full h-full object-contain rounded-b-xl">
          <source src={videoUrl} type="video/mp4" />
          Your browser does not support the video tag.
        </video>
      ) : (
        <button
          onClick={downloadVideo}
          disabled={downloading}
          className="bg-white p-3 rounded-full shadow-lg hover:bg-gray-200"
        >
          {downloading ? "🔄" : "⬇️"}
        </button>
      )}
    </div>
  );
}

export default VideoPreview;
