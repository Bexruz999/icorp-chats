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
    file_name: string,

  }
}
const FilePreview: React.FC<props> = ({ msg_id, media }) => {
  const renderPreview = () => {
    console.log(media);
    switch (true) {
      case (media._ === 'messageMediaPhoto'):
        return <ImagePreview imageUrl={route('messenger.get-media', msg_id)} />;
      case media._ === 'messageMediaDocument' && media.document.mime_type.endsWith('mp4'):
        return <VideoPreview videoUrl={route('messenger.get-media', msg_id)} file_name={media.file_name} />;
      case media._ === 'messageMediaDocument' && (media.document.mime_type.endsWith('mp3') || media.document.mime_type.endsWith('ogg')):
        return (
          <audio controls className="w-full">
            <source src={route('messenger.get-media', msg_id)} type={media.document.mime_type} />
            Your browser does not support the audio element.
          </audio>
        );
      case (media._ === 'messageMediaDocument') && media.document.mime_type === 'image/webp':
        return <ImagePreview imageUrl={route('messenger.get-media', msg_id)} />;
      case (media._ === 'messageMediaDocument'):
        return (
          <div className="p-4 bg-gray-100 rounded-lg">
            <a href={route('messenger.get-media', msg_id)} target="_blank" rel="noopener noreferrer" className="text-blue-500 underline">
              {media.file_name}
            </a>
          </div>
        );
    }
  };

  return renderPreview();
};

export default FilePreview;
