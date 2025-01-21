import MainMenuItem from '@/Components/Menu/MainMenuItem';
import { Building, CircleGauge, MessageCircle, MessagesSquare, Printer, Users } from 'lucide-react';

interface MainMenuProps {
  className?: string;
}

export default function MainMenu({ className }: MainMenuProps) {
  return (
    <div className={className}>
      <MainMenuItem
        text="Dashboard"
        link="dashboard"
        icon={<CircleGauge size={20} />}
      />
      <MainMenuItem
        text="Organizations"
        link="organizations"
        icon={<Building size={20} />}
      />
      <MainMenuItem
        text="Сотрудники"
        link="employees.index"
        icon={<Building size={20} />}
      />
      <MainMenuItem
        text="Contacts"
        link="contacts"
        icon={<Users size={20} />}
      />
      <MainMenuItem
        text="Reports"
        link="reports"
        icon={<Printer size={20} />}
      />
      <MainMenuItem
        text="Messenger"
        link="messengers"
        icon={<MessageCircle size={20} />}
      />
      <MainMenuItem
        text="Settings"
        link="settings"
        icon={<Printer size={20} />}
      />

    </div>
  );
}
