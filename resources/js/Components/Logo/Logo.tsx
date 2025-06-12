import React from 'react';

interface CommniLogoProps extends React.SVGProps<SVGSVGElement> {
}

export default function CommniLogo(props: CommniLogoProps) {
  return (
    <svg
      {...props}
      viewBox="0 0 160 40"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
    >
      <text
        x="0"
        y="28"
        fontFamily="Inter, Arial, sans-serif"
        fontWeight="bold"
        fontSize="32"
        fill="currentColor"
        letterSpacing="2"
      >
        Commni
      </text>
    </svg>
  );
}
