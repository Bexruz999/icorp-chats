import React, {createContext, useState} from 'react';


export const VoiceContext = createContext(false);
export const VoiceProvider = (children: any) => {

  const [voice, setVoice] = useState();


  return (
    <VoiceContext.Provider value={{ voice, setVoice }}>
      {this.context}
    </VoiceContext.Provider>
  );

}
