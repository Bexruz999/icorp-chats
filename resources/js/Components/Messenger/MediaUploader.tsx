
import '../../../css/components/selectFile2.css';
import axios from 'axios';
import Loader from '@/Components/Animations/Loader';
import React, { useEffect, useState } from 'react';

type props = {
  close: any,
  selectedChat: any,
  type?: string
}

const FileUploader: React.FC<props> = ({ close, selectedChat, type = false }) => {
  const [files, setFiles] = useState([]);
  const [caption, setCaption] = useState('');

  useEffect(() => {document.getElementById('fileInput').click()}, [])

  const generateRandomId = function() {
    return Date.now().toString(36) + Math.random().toString(36).substring(2);
  }

  const handleFileChange = (event: any) => {
    const selectedFiles = Array.from(event.target.files).map(file => {
      return {
        file,
        uuid: generateRandomId(),
        status: 'queue'
      };
    });
    setFiles((prevFiles: any) => [...prevFiles, ...selectedFiles]);
    console.log(files, selectedFiles);
  };

  const removeFile = (index:any) => {
    setFiles(files.filter((_, i) => i !== index));
  };

  const getStatus = (code: string, index: any) =>
    code === 'queue' ? <button className="remove-btn" onClick={() => removeFile(index)}>❌</button> :
      code === 'uploading' ? <div className="px-2"><Loader/></div> :
        code === 'success' ? <div className="py-1 px-2">✅</div> : '';

  const handleSubmit = async (e: any) => {
    files.map((file) => {
      if (file.status === 'queue') {
        const data = new FormData();
        data.append('file', file.file);
        data.append('file_uuid', file.uuid);
        data.append('message', caption);
        data.append('peer_id', selectedChat.peer_id);
        setFiles(files.map(oldFile => {
          if (file.uuid === oldFile.uuid) {
            oldFile.status = 'uploading';
          }
          return oldFile;
        }))
        try {
          const response = axios.post(route('messenger.send-media'), data, {
            headers: { 'Content-Type': 'multipart/form-data'},
          }).then((response) => {
            if (response.status === 200 && response.data.success) {
              setFiles(files.map(file => {
                if (file.uuid === response.data.uuid) {file.status ='success';}
                return file;
              }))}
          });

          //console.log(`✅ Fayl yuklandi: ${file.name} — ${response.data.path}`);
        } catch (error) {
          console.error(`❌ Xato: ${file.name}`, error);
        }
        console.log(data, file);
      }
    })
  };

  return (
    <div className="file-uploader-container">
      <div className="file-uploader-popup">
        <h2 className="popup-title">{type ? 'Отправить изображений' : 'Отправить Документы'}</h2>

        {files.length === 0 ? (
          ''
        ) : (
          <div className="file-dropzone">
            {files.map((file, index) => (
              <div key={index} className="file-preview">
                <span>{file.file.name}</span>
                {getStatus(file.status, index)}
              </div>
            ))}
          </div>
        )}
        <input
          type="file"
          id="fileInput"
          multiple
          className="file-input"
          accept={type === 'media' ? "video/mp4, image/jpeg, image/png" : ''}
          onChange={handleFileChange}
        />

        <textarea
          className="caption-input"
          placeholder="Подпись..."
          value={caption}
          onChange={(e) => setCaption(e.target.value)}
        ></textarea>

        <div className="action-buttons">
          <button className="add-btn" onClick={() => document.getElementById('fileInput').click()}>
            Добавить
          </button>
          <button className="cancel-btn" onClick={() => {
            close(false);
            console.log(false);
          }}>
            Закрыть
          </button>
          <button className="send-btn" onClick={handleSubmit}>
            Отправить
          </button>
        </div>
      </div>
    </div>
  );
};

export default FileUploader;
