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
}

export const Card = ({ title, description, image }: CardProps) => {
  return (
    <CCard style={{ width: '10rem', margin: '10px' }}>
      <CCardImage style={{padding: '3px'}} orientation="top" src={image} />
      <CCardBody>
        <CCardTitle>{title}</CCardTitle>
        <CCardText>
          {description}
        </CCardText>
      </CCardBody>
      <CCardBody>
        <CListGroupItem className="border-0">Cras justo odio</CListGroupItem>
      </CCardBody>
      <CCardBody>
        <button className="btn-indigo w-full">kupit</button>
      </CCardBody>
    </CCard>
  )
}
