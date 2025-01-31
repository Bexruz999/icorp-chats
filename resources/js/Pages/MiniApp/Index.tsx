import { Link, useForm, usePage } from '@inertiajs/react';
import MiniAppLayout from '@/Layouts/MiniAppLayout';
import WebApp from '@twa-dev/sdk';
import React, { useState } from 'react';
import { Card } from '@/Components/Cards/Cad';
import { Bot, Category, Product } from '@/types';

const Tab = `${({ active }) => active && `border-bottom: 2px solid black; opacity: 1;`}`;

//const categories = ['Cash', 'Credit Card', 'Bitcoin', 'Bitcoin2'];

function DashboardPage() {

  /*const initDataRaw = WebApp.initData;
  const initData = WebApp.initDataUnsafe;*/

  const { categories, bot, slug } = usePage<{categories: Category[], bot: Bot, slug: string}>().props;

  const { data, setData, errors, post, processing } = useForm({
    name: '',
    basket: [],
    tg_id: WebApp.initData
  });

  function handleSubmit() {
    post(route('basket.create', slug));
  }


  const tabNames = categories.map((c: Category) => {return c.name;});

  //console.log('test', initDataRaw, initData);
  const [active, setActive] = useState(tabNames[0]);
  const [basket, setBasket] = useState({});

  function addToBasket(remove: boolean, id: number) {
    if (basket.hasOwnProperty(id) && basket[id] > 0) {
      setBasket((basket) => {
        const newBasket = { ...basket };
        newBasket[id] = remove ? newBasket[id] - 1 : newBasket[id] + 1;
        data.basket = newBasket;
        return newBasket;
      });
    } else {
      basket[id] = remove ? 0 : 1;
      setBasket((basket) => {
        const newBasket = {...basket };
        newBasket[id] = remove? 0 : 1;
        data.basket = newBasket;
        return newBasket;
      });
    }
  }

  function selectTab({ type }: { type: string }) {
    setActive(type);
    console.log(type);
  }

  function checkBasket() {
    return Object.keys(basket).reduce((total, key) => total + basket[key], 0);
  }

  function sendBasket() {
    fetch(route('basket.create'), )
  }

  const inCartClass: string = "d-flex items-center justify-between";
  const addBtnClass: string = "btn-indigo rounded-lg w-full py-1";

  console.log(data.basket);
  return (
    <>
      <div className="d-flex overflow-scroll p-2 z-10">
        {tabNames.map((type) => (
          <div className={active === type ? 'tab-item active-tab' : 'tab-item'}
               key={type} onClick={() => selectTab({ type: type })}>
            {type}
          </div>
        ))}
      </div>
      <div className="tab-contents">
        {categories.map((tab: Category) => (
          <div className={active === tab.name ? 'tab-content active-content' : 'tab-content'}>

            {tab.products.map((product: Product) => {
              return (
                <Card
                  key={product.id}
                  category={tab}
                  product={product}
                  addToBasket={addToBasket}
                  button={
                    <>
                      <div className={basket[product.id] > 0 ? inCartClass : 'd-none'}>
                        <button onClick={() => addToBasket(true, product.id)} className="btn-indigo rounded-l-lg py-1 px-3">-
                        </button>
                        {basket[product.id]}
                        <button onClick={() => addToBasket(false, product.id)} className="btn-indigo rounded-r-lg py-1 px-3">+
                        </button>
                      </div>
                      <button onClick={() => addToBasket(false, product.id)} className={basket[product.id] > 0 ? "d-none" : addBtnClass}>В корзину</button>
                    </>
                  }
                />
              );
            })}
          </div>
        ))}
      </div>

      <div className={checkBasket() > 0 ? 'd-flex h-12 justify-center fixed bottom-0 right-0 left-0' : 'd-none'}>
        <button onClick={handleSubmit} className="btn-indigo rounded-0 w-full">Перейти в корзину</button>
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
