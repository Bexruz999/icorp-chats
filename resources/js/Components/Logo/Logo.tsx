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
        <tspan>COM</tspan>
        <tspan fill="#0074D9">NI</tspan>
      </text>
      <g transform="translate(120, 0)">
        <path
          d="M16 3
             C22.0751 3 27 6.80558 27 12
             C27 17.1944 22.0751 21 16 21
             C14.3922 21 12.8681 20.7472 11.5 20.3
             L7 23V19.5
             C5.019 17.8056 5 17.1944 5 12
             C5 6.80558 9.92487 3 16 3Z"
          fill="#0074D9"
        />
        <circle cx="12" cy="12" r="1.5" fill="white" />
        <circle cx="16" cy="12" r="1.5" fill="white" />
        <circle cx="20" cy="12" r="1.5" fill="white" />
      </g>
    </svg>
  );
}
