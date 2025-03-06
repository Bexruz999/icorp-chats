import React, { useState, useRef, useEffect, useContext } from 'react';
import { CircleStop, CircleX, Mic, PauseCircle, PlayCircle } from 'lucide-react';
import { VoiceContext } from '@/Components/Messenger/VoiceContext';

interface VoiceMessageProps {
  onAudioRecorded: (audioBlob: any) => void;
}

function formatTime(timeInSeconds) {
  const minutes = Math.floor(timeInSeconds / 60);
  const seconds = Math.floor(timeInSeconds % 60);
  return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
}

function VoiceMessage({ onAudioRecorded }: VoiceMessageProps) {
  // ----- Recording state -----
  const [isRecording, setIsRecording] = useState(false);
  const [audioBlob, setAudioBlob] = useState<Blob | null>(null);
  const [audioBuffer, setAudioBuffer] = useState(null);

  // ----- Player state -----
  const [isPlaying, setIsPlaying] = useState(false);
  const [isPaused, setIsPaused] = useState(false);
  const [currentTime, setCurrentTime] = useState(0);
  const [duration, setDuration] = useState(0);

  // ----- Refs -----
  const mediaRecorderRef = useRef(null);
  const audioChunksRef = useRef([]);
  const audioContextRef = useRef(null);
  const sourceRef = useRef(null);
  const animationFrameRef = useRef(null);
  const [voice, setVoice] = useContext(VoiceContext);

  // Pause qilganda shu offset saqlanadi, resume qilganda shu offsetdan davom etiladi.
  const offsetRef = useRef(0);

  // Ijro start bo‘lgan audioContext.currentTime
  const startTimeRef = useRef(0);

  // ===============================
  // 1) AUDIO YOZISH
  // ===============================
  const startRecording = async () => {

    try {
      const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
      const mediaRecorder = new MediaRecorder(stream);

      mediaRecorder.ondataavailable = (event) => {
        if (event.data.size > 0) {
          audioChunksRef.current.push(event.data);
        }
      };

      mediaRecorder.onstop = () => {
        const blob = new Blob(audioChunksRef.current, { type: 'audio/wav' });
        setAudioBlob(blob);
        audioChunksRef.current = [];

        // Web Audio API ga decode qilish
        if (!audioContextRef.current) {
          audioContextRef.current = new (window.AudioContext ||
            window.webkitAudioContext)();
        }
        const reader = new FileReader();
        reader.readAsArrayBuffer(blob);
        reader.onloadend = () => {
          const arrayBuffer = reader.result;
          audioContextRef.current.decodeAudioData(arrayBuffer, (decodedData) => {
            setAudioBuffer(decodedData);
            setDuration(decodedData.duration);
          });
        };

        // Set Handle
        onAudioRecorded(blob);
      };

      mediaRecorder.start();
      mediaRecorderRef.current = mediaRecorder;
      setIsRecording(true);
      setCurrentTime(0);
    } catch (error) {
      console.error('Mikrofonga ruxsat olishda xatolik:', error);
    }
  };

  const stopRecording = () => {
    if (mediaRecorderRef.current) {
      mediaRecorderRef.current.stop();
      setIsRecording(false);
    }
  };

  const cancelRecording = () => {
    setAudioBlob(null);
    setCurrentTime(0);
    setAudioBuffer(null);
    setDuration(0);
    setIsRecording(false);
    onAudioRecorded(false);
  };

  // ===============================
  // 2) AUDIO IJROSI (PLAY, PAUSE)
  // ===============================

  // Ichki funksiyalar
  const stopAudio = (resetOffset = true) => {
    // Avvalgi ijro bo‘lsa, to‘xtatamiz
    if (sourceRef.current) {
      sourceRef.current.stop();
      sourceRef.current.disconnect();
      sourceRef.current = null;
    }
    cancelAnimationFrame(animationFrameRef.current);

    // resetOffset = false => offset saqlanadi (pause dan resume uchun)
    if (resetOffset) {
      offsetRef.current = 0;
      setCurrentTime(0);
    }
    setIsPlaying(false);
    setIsPaused(false);
  };

  // Audio ijrosini yangidan boshlash yoki pause dan davom ettirish
  const playAudio = () => {
    if (!audioBuffer || !audioContextRef.current) return;

    // Har safar play bosilganda, avval eski source-ni to‘xtatamiz,
    // lekin offset saqlab qolamiz (agar pauzadan qayta bosilayotgan bo‘lsa).
    stopAudio(false);

    const now = audioContextRef.current.currentTime;
    // Yangi source node
    const source = audioContextRef.current.createBufferSource();
    source.buffer = audioBuffer;
    source.connect(audioContextRef.current.destination);

    // offsetRef.current — audio qayerdan davom etishini ko‘rsatadi
    source.start(0, offsetRef.current);
    sourceRef.current = source;

    // Ijro start bo‘lgan vaqt
    startTimeRef.current = now;

    setIsPlaying(true);
    setIsPaused(false);

    // requestAnimationFrame bilan currentTime ni yangilab turamiz
    const updateTime = () => {
      const currentNow = audioContextRef.current.currentTime;
      // Boshlanganidan buyon o‘tgan vaqt + offset
      const elapsed = currentNow - startTimeRef.current + offsetRef.current;

      if (elapsed < duration) {
        setCurrentTime(elapsed);
        animationFrameRef.current = requestAnimationFrame(updateTime);
      } else {
        // Track tugaganda
        setCurrentTime(duration);
        offsetRef.current = 0; // qayta boshidan boshlash uchun
        setIsPlaying(false);
        setIsPaused(false);
        cancelAnimationFrame(animationFrameRef.current);
      }
    };

    animationFrameRef.current = requestAnimationFrame(updateTime);
  };

  // Audio ijrosini pauza qilish
  const pauseAudio = () => {
    if (!sourceRef.current || !isPlaying) return;

    // Hozirgi vaqtdan qancha vaqt o‘tdi
    const now = audioContextRef.current.currentTime;
    const elapsed = now - startTimeRef.current;
    // offsetRef ga qo‘shamiz
    offsetRef.current += elapsed;
    if (offsetRef.current > duration) {
      offsetRef.current = duration;
    }

    // Node-ni to‘xtatamiz
    sourceRef.current.stop();
    sourceRef.current.disconnect();
    sourceRef.current = null;

    setIsPlaying(false);
    setIsPaused(true);
    setCurrentTime(offsetRef.current);
    cancelAnimationFrame(animationFrameRef.current);
  };

  // Component unmount bo‘lganda resurslarni tozalash
  useEffect(() => {
    return () => {
      stopAudio();
      if (audioContextRef.current) {
        audioContextRef.current.close();
      }
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  // ===============================
  // 3) STYLES (Minimal misol)
  // ===============================
  const containerStyle = {
    width: 'calc(100% - 180px)',
    backgroundColor: '#2daae4',
    borderRadius: '8px',
    padding: '10px 15px',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'space-between',
    color: '#fff',
    position: 'absolute',
    zIndex: 11,
    top: '18px',
    left: '50%',
    transform: 'translateX(-50%)'
  };

  const progressContainerStyle = {
    flex: 1,
    height: '6px',
    backgroundColor: 'rgba(255,255,255,0.4)',
    borderRadius: '3px',
    margin: '0 10px',
    position: 'relative'
  };

  const progressBarStyle = {
    position: 'absolute',
    top: 0,
    left: 0,
    height: '100%',
    backgroundColor: '#fff',
    borderRadius: '3px',
    width: duration > 0 ? `${(currentTime / duration) * 100}%` : '0%'
  };
  return (
    <div className="px-2">

      {isRecording ?
        <button onClick={stopRecording}><CircleStop color="red" /></button> :
        <button onClick={audioBuffer ? cancelRecording : startRecording}>
          {audioBuffer ? <CircleX color="red" className="mr-2"/> : <Mic/>}
        </button>

      }

      {(audioBuffer && !isRecording) && (
        <div style={containerStyle}>

          {!isPlaying ? (
            <button
              onClick={playAudio}
              style={{ background: 'transparent', border: 'none', color: '#fff' }}
            >
              <PlayCircle />
            </button>
          ) : (
            <button
              onClick={pauseAudio}
              style={{ background: 'transparent', border: 'none', color: '#fff' }}
            >
              <PauseCircle />
            </button>
          )}

          {/* Progress bar */}
          <div style={progressContainerStyle}>
            <div style={progressBarStyle} />
          </div>

          {/* Current time / Duration */}
          <div style={{ fontSize: '14px', minWidth: '45px', textAlign: 'right', marginLeft: '10px' }}>
            {formatTime(currentTime)} / {formatTime(duration)}
          </div>
        </div>
      )}
    </div>
  );
}

export default VoiceMessage;
