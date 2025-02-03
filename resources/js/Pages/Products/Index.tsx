import { Link, usePage } from '@inertiajs/react';
import MainLayout from '@/Layouts/MainLayout';
import FilterBar from '@/Components/FilterBar/FilterBar';
import Pagination from '@/Components/Pagination/Pagination';
import { PaginatedData, Product } from '@/types';
import Table from '@/Components/Table/Table';
import { Trash2 } from 'lucide-react';

function Index() {
  const { products } = usePage<{
    products: PaginatedData<Product>;
  }>().props;

  const {
    data,
    meta: { links }
  } = products;

  console.log(products);

  return (
    <div>
      <h1 className="mb-8 text-3xl font-bold">Продукции</h1>
      <div className="flex items-center justify-between mb-6">
        <FilterBar />
        {/*<Link
          className="btn-indigo focus:outline-none"
          href={route('products.create')}
        >
          <span>Создать</span>
          <span className="hidden md:inline"> Продукция</span>
        </Link>*/}
      </div>
      <Table
        columns={[
          {
            label: 'Название',
            name: 'name',
            renderCell: row => (
              <>
                {row.image && (
                  <img
                    src={'/storage/'+row.image}
                    alt={row.name}
                    className="w-5 h-5 mr-2 rounded-full"
                  />
                )}
                <>{row.name}</>
              </>
            )
          },
          {
            label: 'Slug',
            name: 'slug',
            renderCell: row => (<>{row.slug}</>)
          },
          {
            label: 'Описание',
            name: 'description',
            renderCell: row => (<>{row.description}</>)
          }
        ]}
        rows={data}
        getRowDetailsUrl={row => route('products.edit', row.id)}
      />
      <Pagination links={links} />
    </div>
  );
}

/**
 * Persistent Layout (Inertia.js)
 *
 * [Learn more](https://inertiajs.com/pages#persistent-layouts)
 */
Index.layout = (page: React.ReactNode) => (
  <MainLayout title="Organizations" children={page} />
);

export default Index;
