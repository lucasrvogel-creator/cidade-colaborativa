<?php
declare(strict_types=1);
namespace App\Services;

final class ImageUpload {
    public static function inspect(string $path, int $size): array {
        if ($size <= 0 || $size > 5 * 1024 * 1024) throw new HttpError(422, 'Cada imagem deve ter no máximo 5 MB.');
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        if (!in_array($mime, ['image/jpeg','image/png','image/webp'], true)) throw new HttpError(422, 'Envie apenas imagens JPEG, PNG ou WebP válidas.');
        $dimensions = @getimagesize($path);
        if (!$dimensions || $dimensions[0] * $dimensions[1] > 16000000) throw new HttpError(422, 'Imagem inválida ou maior que 16 megapixels.');
        return [$mime, $dimensions];
    }
    public static function receive(array $files, int $existing = 0): array {
        $result = [];
        try {
            $errors = $files['error'] ?? [];
            if (!is_array($errors)) throw new HttpError(422, 'Formato de envio de fotos inválido.');
            $count = count(array_filter($errors, fn($error) => $error !== UPLOAD_ERR_NO_FILE));
            if ($count + $existing > 3) throw new HttpError(422, 'São permitidas até três imagens por ocorrência.');
            foreach ($errors as $i => $error) {
                if ($error === UPLOAD_ERR_NO_FILE) continue;
                if ($error !== UPLOAD_ERR_OK || !is_uploaded_file($files['tmp_name'][$i])) throw new HttpError(422, 'Falha no upload. Verifique os limites do PHP e tente novamente.');
                self::inspect($files['tmp_name'][$i], (int)$files['size'][$i]);
                $image = @imagecreatefromstring(file_get_contents($files['tmp_name'][$i]));
                if (!$image) throw new HttpError(422, 'Não foi possível decodificar a fotografia.');
                $name = bin2hex(random_bytes(20)) . '.jpg';
                $width = imagesx($image); $height = imagesy($image); $ratio = min(1, 1920 / max($width, $height));
                $clean = imagecreatetruecolor((int)round($width*$ratio), (int)round($height*$ratio));
                imagefill($clean, 0, 0, imagecolorallocate($clean,255,255,255));
                imagecopyresampled($clean,$image,0,0,0,0,imagesx($clean),imagesy($clean),$width,$height);
                $saved = imagejpeg($clean, ROOT . '/storage/uploads/' . $name, 85);
                imagedestroy($image); imagedestroy($clean);
                if (!$saved) throw new HttpError(500, 'Falha ao salvar a imagem.');
                $result[] = ['caminho_arquivo'=>$name,'tipo_mime'=>'image/jpeg','tamanho_bytes'=>filesize(ROOT . '/storage/uploads/' . $name)];
            }
            return $result;
        } catch (\Throwable $e) { self::cleanup($result); throw $e; }
    }
    public static function cleanup(array $images): void {
        foreach ($images as $image) { $path = ROOT . '/storage/uploads/' . basename($image['caminho_arquivo']); if (is_file($path)) unlink($path); }
    }
}
