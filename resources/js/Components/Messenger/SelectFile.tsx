import React, { useState } from 'react';
import { File, Paperclip, Image } from 'lucide-react';
import '../../../css/components/selectFile.css';
import MediaUploader from '@/Components/Messenger/MediaUploader';

type props = {
  selectedChat: any
}

const DropdownMenu: React.FC<props> = ({ selectedChat }) => {

  const [isDropdownVisible, setDropdownVisible] = useState(false);
  const [isPhotoPopupOpen, setIsPhotoPopupOpen] = useState(false);
  const [isDocumentPopupOpen, setIsDocumentPopupOpen] = useState(false);

  const handleMouseEnter = () => {setDropdownVisible(selectedChat.peer_id);};

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
              <li className="font-bold" onClick={() => setIsPhotoPopupOpen(selectedChat.peer_id)}>
                <File className="pr-3" size="35"/>Фото или видео
              </li>
              <li className="font-bold" onClick={() => setIsDocumentPopupOpen(selectedChat.peer_id)}>
                <Image className="pr-3" size="35"/>Документ
              </li>
            </ul>
          </div>
        }
        {/* Фото или видео pop-up */}
        {isPhotoPopupOpen && (
          <div className="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-20">
            <MediaUploader close={setIsPhotoPopupOpen} selectedChat={selectedChat} type="media"/>
          </div>
        )}

        {/* Dokument pop-up */}
        {isDocumentPopupOpen && (
          <div className="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
            <MediaUploader close={setIsDocumentPopupOpen} selectedChat={selectedChat}/>
          </div>
        )}
      </div>
  );
};

export default DropdownMenu;
