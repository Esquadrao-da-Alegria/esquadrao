import { useState, useCallback } from 'react';
import imageCompression from 'browser-image-compression';
import toast from 'react-hot-toast';

interface CompressorOptions {
    maxSizeMB?: number;
    maxWidthOrHeight?: number;
}


const MB_LIMIT_IN_BYTES = 2 * 1024 * 1024;

export function useImageCompressor(options: CompressorOptions = {}) {
    const [isCompressing, setIsCompressing] = useState(false);
    
    const { maxSizeMB = 1.5, maxWidthOrHeight = 1920 } = options;

    const processImage = useCallback(async (file: File): Promise<File | null> => {
        if (!file) return null;

        const isOverLimit = file.size > MB_LIMIT_IN_BYTES;

        if (isOverLimit) {
            toast('Imagem pesada. Otimizando para o servidor...', { icon: '⏳', id: 'compress' });
        }

        setIsCompressing(true);

        try {
            const compressedFile = await imageCompression(file, {
                maxSizeMB,
                maxWidthOrHeight,
                useWebWorker: true,
            });

            if (isOverLimit) {
                toast.success('Imagem otimizada com sucesso!', { id: 'compress' });
            }

            return compressedFile;

        } catch (error) {
            console.error("Erro na compactação:", error);
            toast.error('Falha ao otimizar. Enviaremos a original.', { id: 'compress' });
            return file; 
            
        } finally {
            setIsCompressing(false);
        }
    }, [maxSizeMB, maxWidthOrHeight]);

    return { processImage, isCompressing };
}