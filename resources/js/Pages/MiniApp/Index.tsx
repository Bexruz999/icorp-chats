import { useForm, usePage } from '@inertiajs/react';
import MiniAppLayout from '@/Layouts/MiniAppLayout';
import WebApp from '@twa-dev/sdk';
import React, { useState } from 'react';
import { Card } from '@/Components/Cards/Cad';
import { Bot, Category, Product } from '@/types';
import { CircleX } from 'lucide-react';
import { CCardText } from '@coreui/react';

function DashboardPage() {

  const {id} = WebApp.initDataUnsafe.user ?? false

  const { categories, slug } = usePage<{categories: Category[], bot: Bot, slug: string}>().props;

  const { data, post } = useForm({
    basket: [],
    tg_id: id ?? 2092452523,
    description: 'description'
  });

  function handleSubmit() {
    post(route('basket.create', slug));
    setTimeout(() => {
      WebApp.close();
    }, 1500);
  }


  const tabNames = categories.map((c: Category) => {return c.name;});

  //console.log('test', initDataRaw, initData);
  const [active, setActive] = useState(tabNames[0]);
  const [basket, setBasket] = useState({});
  const [currentProduct, setCurrentProduct] = useState();

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
  }

  function checkBasket() {
    return Object.keys(basket).reduce((total, key) => total + basket[key], 0);
  }

  function getButton(id) {
    return (
      <>
        <div className={basket[id] > 0 ? inCartClass : 'd-none'}>
          <button onClick={() => addToBasket(true, id)} className="btn-indigo rounded-l-lg py-1 px-3">-
          </button>
          {basket[id]}
          <button onClick={() => addToBasket(false, id)} className="btn-indigo rounded-r-lg py-1 px-3">+
          </button>
        </div>
        <button onClick={() => addToBasket(false,id)} className={basket[id] > 0 ? "d-none" : addBtnClass}>В корзину</button>
      </>
    )
  }

  function showProduct(product: Product) {
    setCurrentProduct(product);
  }

  function getTotalPrice() {
    let total = 0;
    categories.forEach((category) => {
      category.products.forEach((product: Product) => {
        if (basket.hasOwnProperty(product.id)) {
          if (Number(product.price) > Number(product.discount_price) > 0) {
            total += basket[product.id] * product.discount_price;
          } else {
            total += basket[product.id] * product.price;
          }
        }
      })
    });
    return total;
  }

  const inCartClass: string = "d-flex items-center justify-between";
  const addBtnClass: string = "btn-indigo rounded-lg w-full py-1";

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
                  showProduct={showProduct}
                  button={getButton(product.id)}
                />
              );
            })}
          </div>
        ))}
      </div>

      <div className={checkBasket() > 0 ? 'd-flex h-12 justify-center fixed bottom-0 right-0 left-0' : 'd-none'}>
        <button onClick={handleSubmit} className="btn-indigo rounded-0 w-full">Оформить заказ
          | {getTotalPrice()}</button>
      </div>

      <div className={currentProduct ? "product_content" : " d-none"}>
        <div className="product_content-close" onClick={() => setCurrentProduct({})}>
          <CircleX color="white" width="50px" />
        </div>
        <div style={{ background: 'white', padding: '5px', borderRadius: '5px' }}>
          <img width="100%" src={currentProduct ? '/storage/' + currentProduct.image : 'https://img.freepik.com/premium-vector/default-image-icon-vector-missing-picture-page-website-design-mobile-app-no-photo-available_87543-11093.jpg'} />

          <p className="product_content-title"><b>test</b></p>
          <div className="product_content-text">
            <p>fbwifbufu</p>

            <CCardText>Цена:
              <span className={5 > 4 ? 'line-through' : ''}>
            <b>10</b>
          </span>
            </CCardText>

            {(5 > 4) ?
              <CCardText>
                Скидка: <b>9</b>
              </CCardText> : ''}
          </div>

          {getButton(1)}
        </div>
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
