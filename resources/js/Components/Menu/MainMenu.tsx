import MainMenuItem from '@/Components/Menu/MainMenuItem';
import { Building, CircleGauge, MessageCircle, Printer, Users } from 'lucide-react';
import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types';

interface MainMenuProps {
  className?: string;
}

export default function MainMenu({ className }: MainMenuProps) {

  const { auth } = usePage<PageProps>().props

  return (
    <div className={className}>
      <MainMenuItem
        text="Dashboard"
        link="dashboard"
        icon={<CircleGauge size={20} />}
      />
      {/*<MainMenuItem
        text="Organizations"
        link="organizations"
        icon={<Building size={20} />}
      />*/}
      <MainMenuItem
        text="Боты"
        link="bots.index"
        icon={<Building size={20} />}
      />
      <MainMenuItem
        text="Магазины"
        link="shops.index"
        icon={<Building size={20} />}
      />
      <MainMenuItem
        text="Категории"
        link="categories.index"
        icon={<Building size={20} />}
      />
      <MainMenuItem
        text="Сотрудники"
        link="employees.index"
        icon={<Building size={20} />}
      />
      {/*<MainMenuItem
        text="Contacts"
        link="contacts"
        icon={<Users size={20} />}
      />*/}
      <MainMenuItem
        text="Messenger"
        link="messengers"
        icon={<MessageCircle size={20} />}
      />

      {auth.user.owner ? <MainMenuItem text="Settings" link="settings" icon={<Printer size={20} />} /> : ''}

    </div>
  );
}
