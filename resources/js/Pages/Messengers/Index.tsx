import { Link, usePage } from '@inertiajs/react';
import MainLayout from '@/Layouts/MainLayout';
// import Pagination from '@/Components/Pagination/Pagination';
// import FilterBar from '@/Components/FilterBar/FilterBar';
// import { Messenger, PaginatedData } from '@/types';
import { useEffect, useState } from 'react';
import axios from 'axios';


import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { find } from 'lodash';

const MessengerPage = ({ chats }: any) => {

  console.debug(chats);

  let m: object | [] = [];

  const [inputValue, setInputValue] = useState<string>('');
  const [selectedChat, setSelectedChat] = useState(null);
  const [messages, setMessages] = useState(m);
  const [dialogs, setDialogs] = useState([]);

  const [isUserChatsOpen, setIsUserChatsOpen] = useState(true); // Контейнер для "user"
  const [isGroupChatsOpen, setIsGroupChatsOpen] = useState(true); // Контейнер для "chat"

  // Разделение чатов по типу
  const [userChats, setUserChats] = useState(chats.filter((chat) => chat.type === 'user'));
  const [groupChats, setGroupChats] = useState(chats.filter((chat) => chat.type === 'chat'));

  /*setUserChats(chats.filter((chat) => chat.type === 'user'));
  setGroupChats(chats.filter((chat) => chat.type === 'chat'));*/

  // Отправка сообщений
  const handleSendMessage = (event: Event) => {
    if (!inputValue.trim() || !selectedChat || event.key !== 'Enter') return;

    axios
      .post('/messenger/send-message', {
        peerId: selectedChat.peer_id,
        message: inputValue
      })
      .then((response) => {setInputValue('');})
      .catch((error) => {
        console.error('Error sending message:', error);
      });

    // Слушание отправленного сообщения
    window.Echo.private('telegram-message-shipped')
      .listen('TelegramMessageShipped', (response) => {
        console.log(response);
        let shipped = {
          id: response.data.message_id,
          time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
          message: response.data.message,
          user: { self: true, },
          sender: response.data.sender
        };
        setMessages((prevMessages) => [...prevMessages, shipped]);

        // Прокрутка к последнему сообщению
        setTimeout(function() {
          let chat_window = document.getElementById('chat-window');
          chat_window.scrollTo(0, (chat_window.scrollHeight + 1000));
        }, 100);
      });
  };

  const findChat = (peer_id: string) => {
    return chats.find(chat => chat.peer_id === peer_id);
  };


  useEffect(() => {
    if (selectedChat) {
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
        .listen('TelegramMessage', (e) => {
          if (e.message.id === selectedChat.peer_id) {
            e.message.user.first_name = findChat(e.message.id).title;

            console.debug(e.message);
            console.debug(userChats);
            console.debug(groupChats);
            setMessages((prevMessages) => {
              return [...prevMessages, e.message];
            });
            setTimeout(function() {
              let chat_window = document.getElementById('chat-window');
              console.debug(chat_window)
              if (chat_window) {
                chat_window.scrollTo(0, (chat_window.scrollHeight + 1000));
              }
            }, 100);
          }

          // Обновляем диалоги

          let lastChat = false;
          if (e.message.type === 'user') {
            setUserChats((prevChats) => {
              return prevChats.map((chat) => {
                if (chat.peer_id === e.message.id) {
                  chat.last_message = e.message.message;
                  lastChat = true;
                }
                return chat;
              });
            });
          }
          else if(e.message.type === 'chat') {
            setGroupChats((prevChats) => {
              return prevChats.map((chat) => {
                if (chat.peer_id === e.message.id) {
                  chat.last_message = e.message.message;
                  lastChat = true;
                }
                return chat;
              });
            });
          }

            /*if (!lastChat) {
              setChat((prevChats) => [...prevChats, {
                peer_id: e.message.id,
                type: e.message.type,
                title: e.message.user.first_name,
                last_message: e.message.message,
                unread_count: 0
              }]);
            }*/
            console.debug('userChat', userChats);
            console.debug('groupChat', groupChats);
            console.debug('last', lastChat);
            console.debug(e)

        });

    }
  }, [selectedChat]);

  return (
    <div className="flex h-screen">
      {/* Sidebar */}
      <div className="w-1/4 bg-gray-100 p-4 overflow-y-auto max-h-screen">
        <h2 className="text-xl font-bold mb-4">Conversations</h2>

        <div>
          {/* User Chats */}
          <div>
            <div
              className="flex items-center justify-between cursor-pointer mb-2"
              onClick={() => setIsUserChatsOpen(!isUserChatsOpen)}
            >
              <h3 className="font-bold">Private Chats</h3>
              <span>{isUserChatsOpen ? '▲' : '▼'}</span>
            </div>
            {isUserChatsOpen && (
              <div>
                {console.debug(userChats)}
                {userChats.map((chat) => (

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
                {groupChats.map((chat) => (
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

        <div id="chat-window" className="flex-1 p-4 overflow-y-scroll">
          {messages.map((msg, idx) => (
            <div
              key={idx}
              className={`flex ${
                msg.user.self ? 'justify-end' : 'justify-start'
              } mb-4`}
            >
              <div
                className={`relative p-4 rounded-lg max-w-full min-w-40  ${
                  (msg.user.self ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-800')
                }`}
              >
                <p className="text-xs font-bold mb-1">{!msg.user.self ? msg.user.first_name : msg.sender ? msg.sender : msg.user.first_name}</p>
                <p className="text-sm">{msg.message}</p>
                <p className="text-xs mt-1 ${
                  msg.is_self ? ' text-white' : 'text-gray-500'
                }">{msg.time}</p>
              </div>
            </div>
          ))}
        </div>


        <div className="p-4 border-t border-gray-200 flex items-center">
          <input
            type="text"
            value={inputValue}
            onChange={(e) => setInputValue(e.target.value)}
            onKeyPress={handleSendMessage}
            placeholder="Type your message here..."
            className="flex-1 border border-gray-300 rounded-lg p-2 mr-2"
          />
          <button
            onClick={handleSendMessage}
            className="bg-indigo-500 text-white px-4 py-3.5 rounded-lg"
          >
            Send
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
