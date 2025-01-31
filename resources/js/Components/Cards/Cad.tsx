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
  addToBasket: (remove: boolean, id: number) => void
}

export const Card = ({button, product, addToBasket }: CardProps) => {


  return (
    <CCard style={{width: '45%', margin: '10px' }}>
      <CCardImage style={{ padding: '5px', borderRadius: '10px' }} orientation="top" src={'/storage/' + product.image} />
      <CCardBody>
        <div className="d-flex flex-col justify-between h-full">
          <CCardTitle><b>{product.name}</b></CCardTitle>
          <CCardText>{product.description}</CCardText>

          <CCardText>Цена:
            <span className={(product.price > product.discount_price) ? 'line-through' : ''}>
            <b>{product.price}</b>
          </span>
          </CCardText>

          {(product.price > product.discount_price) ?
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
