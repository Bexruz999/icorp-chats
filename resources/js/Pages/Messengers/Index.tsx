import MainLayout from '@/Layouts/MainLayout';
import React, { useEffect, useState } from 'react';
import axios from 'axios';
import SelectFile from '@/Components/Messenger/SelectFile';
import Preview from '@/Components/Messenger/Preview';
import VoiceMessage from '@/Components/Messenger/VoiceMessage';
import { SendHorizonal } from 'lucide-react';
import { VoiceContext } from '@/Components/Messenger/VoiceContext';

declare global {
  interface Window {
    Echo: any;
  }
}

const MessengerPage = ({ chats }: any) => {

  let m: object[] = [];
  let selectedChatType: {
    peer_id: number | boolean,
    title: string | null,
    type: string | null,
  } = {
    peer_id: false,
    title: null,
    type: null
  };

  const [inputValue, setInputValue] = useState<string>('');
  const [recordedAudio, setRecordedAudio] = useState<Blob | null>(null);

  const [selectedChat, setSelectedChat] = useState(selectedChatType);
  const [messages, setMessages] = useState(m);

  const [isUserChatsOpen, setIsUserChatsOpen] = useState(true); // Контейнер для "user"
  const [isGroupChatsOpen, setIsGroupChatsOpen] = useState(true); // Контейнер для "chat"

  // Разделение чатов по типу
  const [userChats, setUserChats] = useState(chats.filter((chat: any) => chat.type === 'user'));
  const [groupChats, setGroupChats] = useState(chats.filter((chat: any) => chat.type === 'chat'));

  // Отправка сообщений
  const handleSendMessage = (event: any) => {

    console.log(recordedAudio);
    if (recordedAudio) {
      handleAudioRecorded(recordedAudio);
      return;
    } else if (
      !inputValue.trim() ||
      !selectedChat ||
      (event.key !== 'Enter' && event.type === 'keydown')
    ) return;

    axios
      .post('/messenger/send-message', {
        peerId: selectedChat.peer_id,
        message: inputValue
      })
      .then(() => {
        setInputValue('');
      })
      .catch((error) => {
        console.error('Error sending message:', error);
      });
  };

  useEffect(() => {
    console.log('eifvh');
    // Слушание отправленного сообщения
    window.Echo.private('telegram-message-shipped')
      .listen('TelegramMessageShipped', (response: any) => {
        setMessages((prevMessages: any) => [...prevMessages, response.data]);
        // Прокрутка к последнему сообщению
        console.log('response', response);
        setTimeout(function() {
          console.log('response:', response);
          let chat_window: any = document.getElementById('chat-window');
          chat_window.scrollTo(0, (chat_window.scrollHeight + 1000));
        }, 100);
      });
  }, []);

  const findChat = (peer_id: string) => {
    return chats.find((chat: any) => chat.peer_id === peer_id);
  };

  // VoiceMessage dan audioBlob kelganda chaqiriladi
  const handleAudioRecorded = (blob: Blob) => {

    console.log(blob);
    const data = new FormData();
    data.append('file', blob, "recorded-audio.ogg");
    data.append('file_uuid', 'audio');
    data.append('message', '');
    data.append('peer_id', selectedChat.peer_id);
    try {
      const response = axios.post(route('messenger.send-voice'), data, {
        headers: { 'Content-Type': 'multipart/form-data' }
      }).then((response) => {
      });
    } catch (error) {
      console.error(`error: `, error);
    }
  };


  useEffect(() => {
    if (selectedChat.peer_id) {
      axios
        .get('/messenger/messages', { params: { peerId: selectedChat.peer_id } })
        .then((response) => {
          setMessages(response.data);

          // Прокрутка к последнему сообщению
          setTimeout(() => {
            const chatContainer = document.querySelector('.flex-1');
            if (chatContainer) {
              chatContainer.scrollTop = chatContainer.scrollHeight;
            }
          }, 100);
        })
        .catch((error) => {
          console.error('Error fetching messages:', error);
        });

      // Слушаем события новых сообщений
      window.Echo.private('telegram-messages')
        .listen('TelegramMessage', (e: any) => {
          if (e.message.chat_id === selectedChat.peer_id) {
            e.message.user.first_name = findChat(e.message.chat_id).title;
            console.log(e);
            setMessages((prevMessages) => {
              return [...prevMessages, e.message];
            });
            setTimeout(function() {
              let chat_window = document.getElementById('chat-window');
              if (chat_window) {
                chat_window.scrollTo(0, (chat_window.scrollHeight + 1000));
              }
            }, 100);
          }

          // Обновляем диалоги

          let lastChat = true;
          if (e.message.type === 'user') {
            setUserChats((prevChats: any) => {
              prevChats = prevChats.map((chat: any) => {
                if (chat.peer_id === e.message.id) {
                  chat.last_message = e.message.message;
                  lastChat = false;
                }
                return chat;
              });
              if (lastChat) {
                prevChats = [...prevChats, {
                  peer_id: e.message.id,
                  type: e.message.type,
                  title: e.message.user.first_name,
                  last_message: e.message.message,
                  unread_count: 0
                }];
              }
              return prevChats;
            });
          } else if (e.message.type === 'chat') {
            setGroupChats((prevChats: any) => {
              return prevChats.map((chat: any) => {
                if (chat.peer_id === e.message.id) {
                  chat.last_message = e.message.message;
                  lastChat = false;
                }
                return chat;
              });
            });
          }

        });

    }
  }, [selectedChat]);

  return (
    <div className="flex" style={{ height: 'calc(100vh - 170px)' }}>
      {/* Sidebar */}
      <div className="w-1/4 bg-gray-100 p-4 overflow-y-auto max-h-screen">
        <h2 className="text-xl font-bold mb-4">Разговоры</h2>

        <div>
          {/* User Chats */}
          <div>
            <div
              className="flex items-center justify-between cursor-pointer mb-2"
              onClick={() => setIsUserChatsOpen(!isUserChatsOpen)}
            >
              <h3 className="font-bold">Приватные чаты</h3>
              <span>{isUserChatsOpen ? '▲' : '▼'}</span>
            </div>
            {isUserChatsOpen && (
              <div>
                {userChats.map((chat: any) => (

                  <div
                    key={chat.id}
                    onClick={() => setSelectedChat(chat)}
                    className={`flex items-center justify-between p-2 rounded-lg cursor-pointer ${
                      selectedChat?.peer_id === chat.id ? 'bg-indigo-100' : ''
                    }`}
                  >
                    <div className="flex items-center space-x-3">
                      <div>
                        <h3 className="font-bold">{chat.title}</h3>
                        <p className="text-sm text-gray-500">{chat.time}</p>
                        <p className="text-sm text-gray-500">
                          {chat.last_message}
                        </p>
                      </div>
                    </div>
                    {chat.unread > 0 &&
                      (<span
                        className="bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                        {chat.unread}
                      </span>)}
                  </div>
                ))}
              </div>
            )}
          </div>

          {/* Group Chats */}
          <div>
            <div
              className="flex items-center justify-between cursor-pointer mt-4"
              onClick={() => setIsGroupChatsOpen(!isGroupChatsOpen)}
            >
              <h3 className="font-bold">Group Chats</h3>
              <span>{isGroupChatsOpen ? '▲' : '▼'}</span>
            </div>
            {isGroupChatsOpen && (
              <div>
                {groupChats.map((chat: any) => (
                  <div
                    key={chat.id}
                    onClick={() => setSelectedChat(chat)}
                    className={`flex items-center justify-between p-2 rounded-lg cursor-pointer ${
                      selectedChat?.peer_id === chat.id ? 'bg-indigo-100' : ''
                    }`}
                  >
                    <div className="flex items-center space-x-3">
                      <div>
                        <h3 className="font-bold">{chat.title}</h3>
                        <p className="text-sm text-gray-500">{chat.time}</p>
                        <p className="text-sm text-gray-500">
                          {chat.last_message.length > 30
                            ? `${chat.last_message.slice(0, 30)}...`
                            : chat.last_message}
                        </p>
                      </div>
                    </div>
                    {chat.unread > 0 && (
                      <span
                        className="bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                      {chat.unread}
                    </span>
                    )}
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>

      </div>

      {/* Chat Window */}
      <div className="w-3/4 flex flex-col bg-white">

        <div className="flex items-center justify-between p-4 border-b border-gray-200">
          <h2 className="text-xl font-bold">{selectedChat?.title || 'Select a chat'}</h2>
        </div>

        {/* Chat Window */}
        <div id="chat-window" className="flex-1 p-4 overflow-y-scroll">
              {console.log(messages)}
          {messages.map((msg: any, idx: any) => (
            <div
              key={idx}
              className={`flex ${msg.user.self ? 'justify-end' : 'justify-start'} mb-4`}
            >
              <div
                className={`relative p-4 rounded-lg max-w-full min-w-96  ${
                  (msg.user.self ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-800')
                }`}
              >
                {console.log(msg)}
                <p className="text-xs font-bold mb-1">{msg.user.first_name}</p>
                {msg.media && <Preview msg_id={msg.id} media={msg.media} />}
                <p className="text-sm">{msg.message}</p>
                <p className="text-xs mt-1">{msg.time}</p>
              </div>
            </div>
          ))}
        </div>

        {/* Send Message */}
        <div className="p-4 border-t border-gray-200 flex items-center" style={{ position: 'relative' }}>
          <SelectFile selectedChat={selectedChat} />
          <VoiceContext>
            <VoiceMessage onAudioRecorded={setRecordedAudio} />
          </VoiceContext>
          <input
            type="text"
            value={inputValue}
            onChange={(e) => setInputValue(e.target.value)}
            onKeyDown={handleSendMessage}
            placeholder="Type your message here..."
            className="flex-1 border border-gray-300 rounded-lg p-2 mx-3 z-10"
          />
          <button
            onClick={handleSendMessage}
            className="bg-sky-500 text-white px-4 py-2 rounded-lg"
          >
            <SendHorizonal size="30" />
          </button>
        </div>

      </div>

    </div>
  );
};

/**
 * Persistent Layout (Inertia.js)
 *
 * [Learn more](https://inertiajs.com/pages#persistent-layouts)
 */
MessengerPage.layout = (page: React.ReactNode) => (
  <MainLayout title="Messenger" children={page} />
);

export default MessengerPage;
