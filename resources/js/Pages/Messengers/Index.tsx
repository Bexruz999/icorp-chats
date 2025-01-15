import { Link, usePage } from '@inertiajs/react';
import MainLayout from '@/Layouts/MainLayout';
// import Pagination from '@/Components/Pagination/Pagination';
// import FilterBar from '@/Components/FilterBar/FilterBar';
// import { Messenger, PaginatedData } from '@/types';
import { useState } from 'react';



const MessengerPage = () => {
  const [selectedChat, setSelectedChat] = useState<string | null>('Oscar Holloway');
  const [inputValue, setInputValue] = useState<string>('');
  const [messages, setMessages] = useState([
    { sender: 'Olive Dixon', time: '12:04 AM', content: 'Hi, Evan! Nice to meet you too.' },
    { sender: 'You', time: '12:15 AM', content: 'Hi, Oscar! Nice to meet you.' },
    { sender: 'Olive Dixon', time: '12:04 AM', content: 'Hi! Please, change the status in this task.' },
  ]);

  const chats = [
    {
      id: '1',
      type: 'group',
      name: 'Medical App Team',
      time: '12:04',
      unread: 12,
    },
    {
      id: '2',
      type: 'group',
      name: 'Food Delivery Service',
      time: '12:04',
      unread: 1,
    },
    {
      id: '3',
      type: 'direct',
      name: 'Garrett Watson',
      time: '12:04',
    },
    {
      id: '4',
      type: 'direct',
      name: 'Oscar Holloway',
      time: '12:04',
    },
  ];

  const handleSendMessage = () => {
    if (inputValue.trim() === '') return;

    const newMessage = {
      sender: 'You',
      time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
      content: inputValue,
    };

    setMessages(prevMessages => [...prevMessages, newMessage]);
    setInputValue('');
  };

  return (
    <div className="flex h-screen">
      {/* Sidebar */}
      <div className="w-1/4 bg-gray-100 p-4">
        <h2 className="text-xl font-bold mb-4">Conversations</h2>
        <div>
          {chats.map(chat => (
            <div
              key={chat.id}
              onClick={() => setSelectedChat(chat.name)}
              className={`flex items-center justify-between p-2 rounded-lg cursor-pointer ${
                selectedChat === chat.name ? 'bg-indigo-100' : ''
              }`}
            >
              <div className="flex items-center space-x-3">
                {/* Icon */}
                <svg
                  width="40"
                  height="40"
                  viewBox="0 0 40 40"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                    d="M39.9904 20.0245C39.9904 31.0544 31.0482 39.9973 20.0177 39.9973C8.98718 39.9969 0.0452888 31.055 0.0449219 20.0245C0.0449219 8.99461 8.98711 0.0517578 20.0177 0.0517578C31.0483 0.0517578 39.9904 8.99387 39.9904 20.0245Z"
                    fill="#28D196"
                  />
                  <path
                    fillRule="evenodd"
                    clipRule="evenodd"
                    d="M23.6201 9.68847C23.6201 9.23013 23.2486 8.85857 22.7902 8.85857L17.5424 8.85791C17.2301 8.85791 16.977 9.11107 16.977 9.42336V11.3503C16.977 11.5704 17.0644 11.7815 17.22 11.9371C17.3757 12.0927 17.5868 12.1802 17.8069 12.1802H22.7902C23.0105 12.1802 23.2217 12.0926 23.3773 11.9369C23.533 11.7811 23.6203 11.5698 23.6201 11.3496V9.68847ZM18.0846 14.3955C16.2498 14.3955 14.7623 12.908 14.7623 11.0732L13.6554 11.0752C12.4326 11.0752 11.4414 12.0664 11.4414 13.2891V28.7861C11.4412 29.3734 11.6744 29.9367 12.0896 30.3521C12.5048 30.7674 13.0681 31.0008 13.6554 31.0008H26.9404C28.1635 31.0008 29.155 30.0093 29.155 28.7861V13.2891C29.1556 12.7018 28.9227 12.1384 28.5078 11.7228C28.0928 11.3072 27.5297 11.0735 26.9424 11.0732H25.8354C25.8354 12.908 24.348 14.3955 22.5132 14.3955H18.0846Z"
                    fill="#F9FAFC"
                  />
                  <g style={{ mixBlendMode: 'overlay' }}>
                    <path
                      d="M39.9911 20.0247C39.9911 31.0546 31.0489 39.9974 20.0184 39.9974C14.7289 40.0042 9.65415 37.9059 5.91406 34.1655L34.1234 5.88379C37.8856 9.62709 39.9979 14.7174 39.9911 20.0247Z"
                      fill="#7B187D"
                    />
                  </g>
                </svg>
                {/* Chat Name */}
                <div>
                  <h3 className="font-bold">{chat.name}</h3>
                  <p className="text-sm text-gray-500">{chat.time}</p>
                </div>
              </div>
              {chat.unread && (
                <span className="bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                  {chat.unread}
                </span>
              )}
            </div>
          ))}
        </div>
      </div>

      {/* Chat Window */}
      <div className="w-3/4 flex flex-col bg-white">
        <div className="flex items-center justify-between p-4 border-b border-gray-200">
          <h2 className="text-xl font-bold">{selectedChat}</h2>
        </div>

        <div className="flex-1 p-4 overflow-y-scroll">
          {messages.map((msg, idx) => (
            <div
              key={idx}
              className={`flex ${
                msg.sender === 'You' ? 'justify-end' : 'justify-start'
              } mb-4`}
            >
              <div className="bg-gray-100 p-2 rounded-lg">
                <p className="text-sm text-gray-700">{msg.content}</p>
                <p className="text-xs text-gray-500 mt-1">{msg.time}</p>
              </div>
            </div>
          ))}
        </div>

        <div className="p-4 border-t border-gray-200 flex items-center">
          <input
            type="text"
            value={inputValue}
            onChange={e => setInputValue(e.target.value)}
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
}

/**
 * Persistent Layout (Inertia.js)
 *
 * [Learn more](https://inertiajs.com/pages#persistent-layouts)
 */
MessengerPage.layout = (page: React.ReactNode) => (
  <MainLayout title="Messenger" children={page} />
);

export default MessengerPage;
