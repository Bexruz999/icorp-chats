import React from 'react'
import {
  CCard,
  CCardBody,
  CCardImage,
  CCardLink,
  CCardText,
  CCardTitle,
  CListGroup,
  CListGroupItem,
} from '@coreui/react'

interface CardProps {
  title: string,
  description: string,
  image: string,
  price: number,
  discount_price: number,
}

export const Card = ({ title, description, image, price, discount_price }: CardProps) => {
  return (
    <CCard style={{ width: '10rem', margin: '10px' }}>
      <CCardImage style={{padding: '3px'}} orientation="top" src={image} />
      <CCardBody>
        <CCardTitle><b>{title}</b></CCardTitle>
        <CCardText>{description}</CCardText>
      </CCardBody>
      <CCardBody>
        <CCardText>Цена: <span className={(price > discount_price) ? 'line-through' : ''}>
          {price}
        </span></CCardText>

        {(price > discount_price) ? <CCardText>Скидка: {discount_price}</CCardText> : ''}
      </CCardBody>
      <CCardBody>
        <button className="btn-indigo w-full">kupit</button>
      </CCardBody>
    </CCard>
  )
}
