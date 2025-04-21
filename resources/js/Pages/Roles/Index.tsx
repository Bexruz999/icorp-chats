import { Link, usePage } from '@inertiajs/react';
import MainLayout from '@/Layouts/MainLayout';
import FilterBar from '@/Components/FilterBar/FilterBar';
import Pagination from '@/Components/Pagination/Pagination';
import { PaginatedData, Role } from '@/types';
import Table from '@/Components/Table/Table';
import { Trash2 } from 'lucide-react';
import CreateDrawer from '@/Pages/Roles/CreateDrawer';

const Index = () => {
  const { roles } = usePage<{ roles: PaginatedData<Role> }>().props;

  const data = roles;

  return (
    <div>
      <h1 className="mb-8 text-3xl font-bold">Roles</h1>
      <div className="flex items-center justify-end mb-6">
        {/*<Link
          className="btn-indigo focus:outline-none"
          href={route('roles.create')}
        >
          <span>Create</span>
          <span className="hidden md:inline"> Role</span>
        </Link>*/}
        <CreateDrawer>

        </CreateDrawer>
      </div>
      <Table
        columns={[
          {
            label: 'Name',
            name: 'name',

            renderCell: row => (<>{row.name}</>)
          },
        ]}
        rows={data}
        getRowDetailsUrl={row => route('users.edit', row.id)}
      />
    </div>
  );
};

/**
 * Persistent Layout (Inertia.js)
 *
 * [Learn more](https://inertiajs.com/pages#persistent-layouts)
 */
Index.layout = (page: React.ReactNode) => (
  <MainLayout title="Users" children={page} />
);

export default Index;
