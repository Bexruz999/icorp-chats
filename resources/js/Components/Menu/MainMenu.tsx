import MainMenuItem from '@/Components/Menu/MainMenuItem';
import { Building, CircleGauge, MessageCircle, Settings } from 'lucide-react';
import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types';

interface MainMenuProps {
  className?: string;
}

export default function MainMenu({ className }: MainMenuProps) {

  const { auth } = usePage<PageProps>().props;

  const hasPermission = (perm: string) => {
    return auth.permissions?.includes(perm);
  };
  const hasRole = (perm: string) => {
    return auth.roles?.includes(perm);
  };

  return (
    <div className={className}>
      {/*<MainMenuItem text="Organizations" link="organizations" icon={<Building size={20} />}/>*/}
      {/*<MainMenuItem text="Категории" link="categories.index" icon={<Building size={20} />}/>*/}
      {/*<MainMenuItem text="Contacts" link="contacts" icon={<Users size={20} />}/>*/}

      <MainMenuItem text="Dashboard"  link="dashboard"        icon={<CircleGauge/>}/>
      <MainMenuItem text="Боты"       link="bots.index"       icon={<Building/>}/>
      <MainMenuItem text="Магазины"   link="shops.index"      icon={<Building/>}/>
      <MainMenuItem text="Продукция"  link="products.index"   icon={<Building/>}/>
      <MainMenuItem text="Сотрудники" link="employees.index"  icon={<Building/>}/>
      <MainMenuItem text="Роли"       link="roles.index"      icon={<Building/>}/>
      <MainMenuItem text="Messenger"  link="messengers"       icon={<MessageCircle/>}/>

      {hasRole('admin') && <MainMenuItem text="Settings" link="settings" icon={<Settings/>}/>}
    </div>
  );
}
