import React, { useState } from 'react';
import '../../../css/components/selectFile2.css';
import axios from 'axios';

const FileUploader = ({ close }) => {
  const [files, setFiles] = useState([]);
  const [caption, setCaption] = useState('');
  const [compress, setCompress] = useState(true);

  const generateRandomId = function() {
    return Date.now().toString(36) + Math.random().toString(36).substring(2);
  }

  const handleFileChange = (event) => {
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

  const removeFile = (index) => {
    setFiles(files.filter((_, i) => i !== index));
  };

  const handleSubmit = async (e) => {
    files.map((file) => {
      const data = new FormData();
      data.append('file', file.file);
      data.append('file_uuid', file.uuid);
      setFiles(files.map(file => {
        if (file.uuid === file.uuid) {
          file.status = 'uploading';
        }
        return file;
      }))
      try {
        const response = axios.post(route('messenger.send-media'), data, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        });

        setFiles(files.map(file => {
          if (file.uuid === file.uuid) {file.status ='success';}
          return file;
        }))
        //console.log(`✅ Fayl yuklandi: ${file.name} — ${response.data.path}`);
      } catch (error) {
        console.error(`❌ Xato: ${file.name}`, error);
      }
      console.log(data, file);
    })
  };

  return (
    <div className="file-uploader-container">
      <div className="file-uploader-popup">
        <h2 className="popup-title">Отправить изображений</h2>

        {files.length === 0 ? (
          ''
        ) : (
          <div className="file-dropzone">
            {files.map((file, index) => (
              <div key={index} className="file-preview">
                <span>{file.file.name}</span>
                <button className="remove-btn" onClick={() => removeFile(index)}>
                  {file.status}
                </button>
              </div>
            ))}
          </div>
        )}
        <input
          type="file"
          id="fileInput"
          multiple
          className="file-input"
          onChange={handleFileChange}
        />

        <label className="compress-checkbox">
          <input
            type="checkbox"
            checked={compress}
            onChange={(e) => setCompress(e.target.checked)}
          />
          Сжать изображение
        </label>

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
          <button className="cancel-btn" onClick={() => close(false)}>
            Отмена
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
