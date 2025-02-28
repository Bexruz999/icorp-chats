import React from 'react';
import ImagePreview from '@/Components/Messenger/ImagePreview';
import VideoPreview from '@/Components/Messenger/videoPreview';

type props = {
  msg_id: number,
  media: {
    _: string,
    document: {
      attributes: {};
      mime_type: string;
    },
    caption: string
  }
}
const FilePreview: React.FC<props> = ({ msg_id, media }) => {
  const renderPreview = () => {
    switch (true) {
      case (media._ === 'messageMediaPhoto'):
        return <ImagePreview imageUrl={route('messenger.get-media', msg_id)} />;
      case media._ === 'messageMediaDocument' && media.document.mime_type.endsWith('mp4'):
        return <VideoPreview videoUrl={route('messenger.get-media', msg_id)} />;
      case media._ === 'messageMediaDocument' && media.document.mime_type.endsWith('mp3'):
        return <div>Audio preview</div>;
      case (media._ === 'messageMediaDocument') && media.document.mime_type === 'image/webp':
        return <ImagePreview imageUrl={route('messenger.get-media', msg_id)} />;
      case (media._ === 'messageMediaDocument'):
        return (
          <div className="p-4 bg-gray-100 rounded-lg">
            <a href={route('messenger.get-media', msg_id)} target="_blank" rel="noopener noreferrer" className="text-blue-500 underline">
              View File
            </a>
          </div>
        );
    }
  };

  return renderPreview();
};

export default FilePreview;
