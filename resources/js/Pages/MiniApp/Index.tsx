import { Link, usePage } from '@inertiajs/react';
import MiniAppLayout from '@/Layouts/MiniAppLayout';
import WebApp from '@twa-dev/sdk';
import { useState } from 'react';
import { Card } from '@/Components/Cards/Cad';
import { Category, Product } from '@/types';

const Tab = `${({ active }) => active && `border-bottom: 2px solid black; opacity: 1;`}`;

//const categories = ['Cash', 'Credit Card', 'Bitcoin', 'Bitcoin2'];


function DashboardPage() {

  const { data } = usePage<any>().props
  const categories = data.map((c: Category) => {return c.name;})

  const initDataRaw = WebApp.initData;
  const initData = WebApp.initDataUnsafe;

  console.log('test', initData);
  console.log('test2', initDataRaw);
  const [active, setActive] = useState(categories[0]);
  function selectTab({ type }: { type: string }) {
    setActive(type);
    console.log(type);
  }


  return (
    <>
      <div className="d-flex overflow-scroll p-2 z-10">
        {categories.map((type) => (
          <div className={active === type ? "tab-item active-tab" : "tab-item"}
               key={type} onClick={() => selectTab({ type: type })}>
            {type}
          </div>
        ))}
      </div>
      <div className="tab-contents">
        {data.map((tab: Category) => (
          <div className={active === tab.name ? "tab-content active-content" :"tab-content"}>

            {tab.products.map((product: Product) => {
              return (
                <Card title={product.name}
                      description={product.description}
                      image={'/storage/'+product.image}
                      price={Number(product.price)}
                      discount_price={Number(product.discount_price)}
                />
              );
            })}
          </div>
        ))}
      </div>
    </>
  );
}

/**
 * Persistent Layout (Inertia.js)
 *
 * [Learn more](https://inertiajs.com/pages#persistent-layouts)
 */
DashboardPage.layout = (page: React.ReactNode) => (
  <MiniAppLayout title="Mini App" children={page} />
);

export default DashboardPage;
