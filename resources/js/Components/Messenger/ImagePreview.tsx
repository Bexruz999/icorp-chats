import React, { useState } from "react";

type props = { imageUrl: string }
const ImagePreview: React.FC<props> = ({ imageUrl}) =>{
  const [downloading, setDownloading] = useState(false);
  const [downloaded, setDownloaded] = useState(false);

  const downloadImage = async () => {
    try {

      setDownloading(true);

      // Download file
      await fetch(imageUrl);
      //const blob = await response.blob();
      //const url = URL.createObjectURL(blob);

      // Save file
      /*const link = document.createElement("a");
      link.href = url;
      link.download = `telegram_image_${messageId}.jpg`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);

      URL.revokeObjectURL(url);*/

      setDownloaded(true);
    } catch (error) {
      console.error("Image download failed:", error);
    } finally {
      setDownloading(false);
    }
  };

  const checkImageInCache = async (url: string):Promise<void> => {
    try {
      const response = await fetch(url, {
        method: 'GET',
        cache: 'only-if-cached',
        mode: 'same-origin' // CORS is necessary because of restrictions
      });

      if (response && response.ok) {
        setDownloaded(true)
      }
    } catch (error) {
    }
  }

  checkImageInCache(imageUrl);

  return (
    <div className="relative bg-black h-60 flex items-center justify-center rounded-b rounded-t w-full">
      {downloaded ? (
        <img src={imageUrl} alt="Downloaded" className="w-full h-full object-contain rounded-b-xl" />
      ) : (
        <button
          onClick={downloadImage}
          disabled={downloading}
          className="bg-white p-3 rounded-full shadow-lg hover:bg-gray-200"
        >
          {downloading ? "🔄" : "⬇️"}
        </button>
      )}
    </div>
  );
}

export default ImagePreview;
