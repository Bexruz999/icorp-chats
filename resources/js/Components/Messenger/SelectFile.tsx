import React, { useState } from 'react';
import { File, Paperclip, Image } from 'lucide-react';
import '../../../css/components/selectFile.css';
import { sleep } from 'telegram/Helpers';
import MediaUploader from '@/Components/Messenger/MediaUploader';

const DropdownMenu = () => {

  const [isDropdownVisible, setDropdownVisible] = useState(false);
  const [isPhotoPopupOpen, setIsPhotoPopupOpen] = useState(false);
  const [isDocumentPopupOpen, setIsDocumentPopupOpen] = useState(false);

  const handleMouseEnter = () => {setDropdownVisible(true);};

  const handleMouseLeave = () => {
    setTimeout(() => {setDropdownVisible(false);}, 300)
  };



  return (
      <div className="select-file">
        <Paperclip
          className="select-file_btn"
          onMouseEnter={handleMouseEnter}
        />
        {isDropdownVisible &&
          <div className="select-file_menu" onMouseLeave={handleMouseLeave}>
            <ul>
              <li className="font-bold" onClick={() => setIsPhotoPopupOpen(true)}>
                <File className="pr-3" size="35"/>Фото или видео
              </li>
              <li className="font-bold" onClick={() => setIsDocumentPopupOpen(true)}>
                <Image className="pr-3" size="35"/>Документ
              </li>
            </ul>
          </div>
        }
        {/* Фото или видео pop-up */}
        {isPhotoPopupOpen && (
          <div className="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-20">
            <MediaUploader close={setIsPhotoPopupOpen}/>
          </div>
        )}

        {/* Dokument pop-up */}
        {isDocumentPopupOpen && (
          <div className="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
            <div className="bg-white p-6 rounded-lg shadow-lg max-w-md">
              <h2 className="text-xl font-semibold mb-4">Dokument yuklash</h2>
              <p>Bu yerda hujjatlar yuklash oynasi bo'ladi.</p>
              <button
                className="mt-4 bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600"
                onClick={() => setIsDocumentPopupOpen(false)}
              >
                Yopish
              </button>
            </div>
          </div>
        )}
      </div>
  );
};

export default DropdownMenu;
