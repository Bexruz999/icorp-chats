import { Link, usePage } from '@inertiajs/react';
import MainLayout from '@/Layouts/MainLayout';
// import Pagination from '@/Components/Pagination/Pagination';
// import FilterBar from '@/Components/FilterBar/FilterBar';
// import { Messenger, PaginatedData } from '@/types';
import { useEffect, useState } from 'react';
import axios from 'axios';

const MessengerPage = ({ chats }) => {
  const [inputValue, setInputValue] = useState<string>('');
  const [selectedChat, setSelectedChat] = useState(null); // Выбранный чат
  const [messages, setMessages] = useState([]); // Сообщения чата


  const [isUserChatsOpen, setIsUserChatsOpen] = useState(true); // Контейнер для "user"
  const [isGroupChatsOpen, setIsGroupChatsOpen] = useState(true); // Контейнер для "chat"


  useEffect(() => {
    if (selectedChat) {
      axios
        .get('/messenger/messages', { params: { peerId: selectedChat.id } })
        .then((response) => {
          setMessages(response.data); // Устанавливаем загруженные сообщения
        })
        .catch((error) => {
          console.error('Error fetching messages:', error); // Обрабатываем ошибки
        });
    }
  }, [selectedChat]); // Срабатывает, когда меняется selectedChat


  // Разделение чатов по типу
  const userChats = chats.filter((chat) => chat.type === 'user');
  const groupChats = chats.filter((chat) => chat.type === 'chat');


  const handleSendMessage = () => {
    if (inputValue.trim() === '') return;

    const newMessage = {
      sender: 'You',
      time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
      content: inputValue,
    };

    setMessages((prevMessages) => [...prevMessages, newMessage]);
    setInputValue('');
  };

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
                {userChats.map((chat) => (
                  <div
                    key={chat.id}
                    onClick={() => setSelectedChat(chat)}
                    className={`flex items-center justify-between p-2 rounded-lg cursor-pointer ${
                      selectedChat?.id === chat.id ? 'bg-indigo-100' : ''
                    }`}
                  >
                    <div className="flex items-center space-x-3">
                      <div>
                        <h3 className="font-bold">{chat.name}</h3>
                        <p className="text-sm text-gray-500">{chat.time}</p>
                        <p className="text-sm text-gray-500">
                          {chat.lastMessage.length > 30
                            ? `${chat.lastMessage.slice(0, 30)}...`
                            : chat.lastMessage}
                        </p>
                      </div>
                    </div>
                    {chat.unread > 0 && (
                      <span className="bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
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
                      selectedChat?.id === chat.id ? 'bg-indigo-100' : ''
                    }`}
                  >
                    <div className="flex items-center space-x-3">
                      <div>
                        <h3 className="font-bold">{chat.name}</h3>
                        <p className="text-sm text-gray-500">{chat.time}</p>
                        <p className="text-sm text-gray-500">
                          {chat.lastMessage.length > 30
                            ? `${chat.lastMessage.slice(0, 30)}...`
                            : chat.lastMessage}
                        </p>
                      </div>
                    </div>
                    {chat.unread > 0 && (
                      <span className="bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
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
          <h2 className="text-xl font-bold">{selectedChat?.name || 'Select a chat'}</h2>
        </div>

        <div className="flex-1 p-4 overflow-y-scroll">
          {messages.map((msg, idx) => (
            <div
              key={idx}
              className={`flex ${
                msg.sender === 'You' ? 'justify-end' : 'justify-start'
              } mb-4`}
            >
              <div
                className={`p-3 rounded-lg max-w-xs ${
                  msg.sender === 'You' ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-800'
                }`}
              >
                <p className="text-sm">{msg.content}</p>
                <p className="text-xs mt-1 text-gray-500">{msg.time}</p>
              </div>
            </div>
          ))}
        </div>

        <div className="p-4 border-t border-gray-200 flex items-center">
          <input
            type="text"
            value={inputValue}
            onChange={(e) => setInputValue(e.target.value)}
            placeholder="Type your message here..."
            className="flex-1 border border-gray-300 rounded-lg p-2 mr-2"
          />
          <button
            onClick={handleSendMessage}
            className="bg-indigo-500 text-white px-4 py-2 rounded-lg"
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
