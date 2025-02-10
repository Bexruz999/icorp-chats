import React from 'react';
import {
  CCard,
  CCardBody,
  CCardImage,
  CCardText,
  CCardTitle
} from '@coreui/react';
import { Category, Product } from '@/types';

interface CardProps {
  button: any,
  category: Category,
  product: Product,
  showProduct: void,
}

export const Card = ({button, product, showProduct }: CardProps) => {


  return (
    <CCard onClick={() => showProduct(product)} style={{width: '45%', margin: '10px' }}>
      <CCardImage style={{ padding: '5px', borderRadius: '10px' }} orientation="top" src={product.image ? '/storage/' + product.image : 'https://img.freepik.com/premium-vector/default-image-icon-vector-missing-picture-page-website-design-mobile-app-no-photo-available_87543-11093.jpg'} />
      <CCardBody>
        <div className="d-flex flex-col justify-between h-full">
          <CCardTitle><b>{product.name}</b></CCardTitle>
          <CCardText>{product.short_description}</CCardText>

          <CCardText>Цена:
            <span className={(Number(product.price) > Number(product.discount_price)) ? 'line-through' : ''}>
            <b>{product.price}</b>
          </span>
          </CCardText>

          {(Number(product.price) > Number(product.discount_price)) ?
            <CCardText>
              Скидка: <b>{product.discount_price}</b>
            </CCardText>
            :''}

          {button}
        </div>
      </CCardBody>
    </CCard>
  );
};
