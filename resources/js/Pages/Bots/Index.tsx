import { Link, usePage } from '@inertiajs/react';
import MainLayout from '@/Layouts/MainLayout';
import FilterBar from '@/Components/FilterBar/FilterBar';
import Pagination from '@/Components/Pagination/Pagination';
import { Bot, PaginatedData } from '@/types';
import Table from '@/Components/Table/Table';
import { Trash2 } from 'lucide-react';

const Index = () => {
  const { bots } = usePage<{ bots: PaginatedData<Bot> }>().props;

  const {
    data,
    meta: { links }
  } = bots;

  return (
    <div>
      <h1 className="mb-8 text-3xl font-bold">
        Боты
      </h1>
      <div className="flex items-center justify-between mb-6">
        {/*<FilterBar />*/}
        <Link className="btn-indigo focus:outline-none" href={route('bots.create')}>
          <span>Создать</span>
        </Link>
      </div>
      <Table
        columns={[
          {
            label: 'Name',
            name: 'name',

            renderCell: row => (
              <>{row.name}</>
            )
          }
        ]}
        rows={data}
        getRowDetailsUrl={row => route('bots.edit', row.id)}
      />
      <Pagination links={links} />
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
