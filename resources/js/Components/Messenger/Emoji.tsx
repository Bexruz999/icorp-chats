import React, { useState } from 'react';
import Picker from '@emoji-mart/react';
import data from '@emoji-mart/data';
import { Smile } from 'lucide-react';

const EmojiInputExample: React.FC<{selectedChat: any, setInput: any}>=({ selectedChat, setInput }) => {
  const [showPicker, setShowPicker] = useState<boolean>(false);

  const handleEmojiSelect = (emoji: any): void => {
    setInput(emoji.native)
  };

  const handleMouseEnter = () => {
    console.log(selectedChat);
    if (selectedChat.peer_id !== false) {
      setShowPicker(true);
    }
  };

  const handleMouseLeave = () => {
    setShowPicker(false);
  };

  return (
    <div style={{ position: 'relative' }}>

      {/* Emoji Picker and Emoji Btn */}
      <div onMouseEnter={handleMouseEnter} onMouseLeave={handleMouseLeave} style={{ display: 'inline-block', marginLeft: '10px', position: 'relative' }}>
        <button style={{ fontSize: '20px' }}>
          <Smile />
        </button>
        {showPicker && (
          <div style={{ position: 'absolute', bottom: '25px', zIndex: 1000 }}>
            <Picker data={data} onEmojiSelect={handleEmojiSelect} />
          </div>
        )}
      </div>
    </div>
  );
};

export default EmojiInputExample;
