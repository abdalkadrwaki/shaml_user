<?php

namespace App\Services;

use App\Models\Transfer;
use App\Models\User;
use ArPHP\I18N\Arabic;
use Exception;
use Carbon\Carbon;

class GenerateTransferImageService
{
    protected $arabic;
    protected $fontPath;
    protected $fontPathhh;
    protected $fontPathh;
    protected $fontPathhhh;
    // متغير لتخزين صورة الخلفية المُحمّلة (كاش)
    protected static $bgImageCached = null;

    public function __construct()
    {
        // تحميل مكتبة العربية وتهيئة مسارات الخطوط مرة واحدة
        $this->arabic = new Arabic();
        $this->fontPath   = public_path('fonts/Alexandria-Regular.ttf');
        $this->fontPathhh = public_path('fonts/Alexandria-Regular.ttf');
        $this->fontPathh  = public_path('fonts/Alexandria-Regular.ttf');
        $this->fontPathhhh   = public_path('fonts/NotoKufiArabic-VariableFont_wght.ttf');

        if (!file_exists($this->fontPath) || !file_exists($this->fontPathhh) || !file_exists($this->fontPathh)) {
            throw new Exception('الخطوط غير موجودة.');
        }
    }

    /**
     * دالة لتقسيم النص إلى أسطر بناءً على عرض محدد بالبكسل.
     *
     * @param string $text النص الأصلي
     * @param int $maxWidth الحد الأقصى للعرض بالبكسل
     * @param string $fontPath مسار الخط
     * @param int $fontSize حجم الخط
     * @param int $angle زاوية الكتابة (افتراضي 0)
     * @return array مصفوفة تحتوي على الأسطر
     */
    protected function splitTextIntoLines($text, $maxWidth, $fontPath, $fontSize, $angle = 0)
    {
        // تقسيم النص إلى كلمات
        $words = explode(" ", $text);
        $lines = [];
        $currentLine = "";

        foreach ($words as $word) {
            // تكوين سطر اختبار بإضافة الكلمة الحالية
            $testLine = ($currentLine === "" ? $word : $currentLine . " " . $word);
            // حساب عرض السطر باستخدام imagettfbbox
            $bbox = imagettfbbox($fontSize, $angle, $fontPath, $testLine);
            $lineWidth = abs($bbox[2] - $bbox[0]);

            if ($lineWidth > $maxWidth) {
                // إذا كان السطر الاختباري يتجاوز الحد، يتم حفظ السطر الحالي وإعادة تعيينه
                if ($currentLine !== "") {
                    $lines[] = $currentLine;
                    $currentLine = $word;
                } else {
                    // حالة خاصة: الكلمة نفسها تتجاوز الحد، فيتم إضافتها مباشرة
                    $lines[] = $word;
                    $currentLine = "";
                }
            } else {
                $currentLine = $testLine;
            }
        }
        if ($currentLine !== "") {
            $lines[] = $currentLine;
        }
        return $lines;
    }

    /**
     * إنشاء صورة الحوالة وتحويلها إلى Base64.
     *
     * @param int $transferId
     * @return string
     * @throws Exception
     */
    public function generateTransferImage($transferId)
    {
        // جلب بيانات الحوالة من قاعدة البيانات
        $transferData = Transfer::find($transferId);
        if (!$transferData) {
            throw new Exception('لم يتم العثور على بيانات الحوالة.');
        }

        // جلب بيانات المستخدمين (المرسل والمستقبل)
        $userAddress = User::where('id', $transferData->destination)->value('user_address');
        $userw = User::find($transferData->destination, ['Office_name']);
        $userw2 = User::find($transferData->destination, ['Office_name']);
        $userm = User::find($transferData->user_id, ['Office_name']);

        if (!$userm || !$userw) {
            throw new Exception('بيانات المستخدمين غير موجودة.');
        }
        $userAddress1 = "{$userw2->Office_name}";
        $userAddressm = "{$userm->Office_name}";
        $userAddressw = "{$userw->Office_name}";

        if (!$userAddress) {
            throw new Exception('لم يتم العثور على عنوان المستخدم.');
        }

        // إنشاء صورة باستخدام مكتبة GD
        $width  = 800;
        $height = 600;
        $image  = imagecreatetruecolor($width, $height);
        if (!$image) {
            throw new Exception('فشل في إنشاء الصورة باستخدام GD.');
        }

        // تحديد الألوان
        $backgroundColor  = imagecolorallocate($image, 255, 255, 255);
        $borderColor      = imagecolorallocate($image, 200, 200, 200);
        $textColor        = imagecolorallocate($image, 5, 5, 5);
        $highlightColor   = imagecolorallocate($image, 255, 0, 0);
        $headerColor      = imagecolorallocate($image, 48, 25, 101);
        $headerColor_so   = imagecolorallocate($image, 140, 140, 140);
        $headerColor_soo  = imagecolorallocate($image, 46, 125, 50);
        $headerColor_sooo = imagecolorallocate($image, 255, 255, 255);

        // تعبئة الخلفية باللون الأبيض
        imagefill($image, 0, 0, $backgroundColor);

        // تحميل الصورة الخلفية من الكاش أو من القرص إذا لم تكن موجودة مسبقاً
        if (self::$bgImageCached === null) {
            self::$bgImageCached = @imagecreatefrompng(public_path('images/imge.png'));
            if (!self::$bgImageCached) {
                throw new Exception('فشل في تحميل الصورة الخلفية.');
            }
        }
        // ضبط أبعاد الصورة الخلفية
        $newWidth  = $width;
        $newHeight = $height;
        // تعديل لون الخلفية بشكل سريع
        imagefilter(self::$bgImageCached, IMG_FILTER_COLORIZE, 255, 255, 255, -15);
        imagecopyresampled($image, self::$bgImageCached, 0, 50, 0, 0, $newWidth, $newHeight, imagesx(self::$bgImageCached), imagesy(self::$bgImageCached));

        // رسم المستطيلات والخطوط
        imagefilledrectangle($image, 11, 520, 788, 451, $headerColor_sooo);
        imageline($image, 30, 90, 770, 90, $borderColor);
        imageline($image, 30, 163, 770, 163, $borderColor);
        imageline($image, 30, 266, 770, 266, $borderColor);

        // إضافة اللوجو إلى الصورة
        $logoPath = public_path('images/image-removebg-preview (2).png');

        if (file_exists($logoPath)) {
            // تحميل صورة اللوجو
            $logo = imagecreatefrompng($logoPath);

            // تحديد الأبعاد المطلوبة للوجو (170x170 بكسل)
            $logoWidth = 170;
            $logoHeight = 170;

            // إنشاء صورة جديدة لتعديل حجم اللوجو
            $logoResized = imagecreatetruecolor($logoWidth, $logoHeight);

            // دعم الشفافية للوجو
            imagecolortransparent($logoResized, imagecolorallocatealpha($logoResized, 0, 0, 0, 127));
            imagealphablending($logoResized, false);
            imagesavealpha($logoResized, true);

            // نسخ وتغيير حجم اللوجو
            imagecopyresampled(
                $logoResized,
                $logo,
                0, 0, // إحداثيات الوجهة في صورة اللوجو المعدلة
                0, 0, // إحداثيات المصدر في صورة اللوجو الأصلية
                $logoWidth, $logoHeight, // العرض والارتفاع المطلوبين
                imagesx($logo), imagesy($logo)
            );

            // إزالة نسخة اللوجو الأصلية من الذاكرة
            imagedestroy($logo);

            // تحديد موقع وضع اللوجو على الصورة الأساسية (مثلاً في الركن العلوي الأيسر)
            $logoX = 30;  // المسافة من اليسار
            $logoY = 30;  // المسافة من الأعلى

            // دمج اللوجو على الصورة الأساسية
            imagecopy(
                $image,
                $logoResized,
                $logoX, $logoY, // إحداثيات الوجهة في الصورة الأساسية
                0, 0,         // إحداثيات المصدر في صورة اللوجو
                $logoWidth, $logoHeight // الأبعاد
            );

            // إزالة نسخة اللوجو المعدلة من الذاكرة
            imagedestroy($logoResized);
        } else {
            // يمكنك تسجيل خطأ أو متابعة التنفيذ بدون اللوجو
            // throw new Exception('ملف اللوجو غير موجود.');
        }

        // دالة داخلية لكتابة النص على الصورة مع الحفاظ على ترتيب الكلمات
        $writeText = function($fontSize, $angle, $x, $y, $color, $fontPath, $text, $alignRight = true) use ($image) {
            // معالجة النص باستخدام ArPHP للحفاظ على ترتيب الكلمات
            $processedText = $this->arabic->utf8Glyphs($text, true);
            // حساب عرض النص باستخدام imagettfbbox
            $bbox = imagettfbbox($fontSize, $angle, $fontPath, $processedText);
            $textWidth = abs($bbox[2] - $bbox[0]);
            // ضبط موضع النص بناءً على الاتجاه
            $newX = $alignRight ? ($x - $textWidth) : $x;
            // كتابة النص على الصورة
            imagettftext($image, $fontSize, $angle, $newX, $y, $color, $fontPath, $processedText);
        };

        // كتابة النصوص الثابتة على الصورة
        $writeText(20, 0, 490, 57, $headerColor, $this->fontPath, "  مكتب  "  . $userAddress1 );

        $writeText(17, 0, 420, 119, $headerColor_so, $this->fontPathhh, "التاريخ");
        imagettftext($image, 13, 0, 290, 150, $textColor, $this->fontPath, Carbon::parse($transferData->created_at)->format('Y-m-d H:i:s'));

        $writeText(17, 0, 770, 119, $headerColor_so, $this->fontPathhh, "المصدر");
        $writeText(13, 0, 770, 150, $headerColor_so, $this->fontPath, $userAddressm);

        $writeText(17, 0, 150, 119, $headerColor_so, $this->fontPathhh, "الوجهة");
        $writeText(13, 0, 150, 150, $headerColor_so, $this->fontPath, $userAddressw);

        $writeText(15, 0, 770, 200, $headerColor_so, $this->fontPathhh, "المستفيد");
        // تقسيم اسم المستفيد إلى أسطر إذا كان طويلاً
        $maxWidth = 200; // عرض السطر بالبكسل الذي تريد الالتزام به
        $lines = $this->splitTextIntoLines($transferData->recipient_name, $maxWidth, $this->fontPath, 17);
        $y = 240; // الإحداثية الرأسية للسطر الأول
        $lineHeight = 20; // المسافة بين الأسطر
        foreach ($lines as $line) {
            $writeText(17, 0, 770, $y, $textColor, $this->fontPath, $line);
            $y += $lineHeight;
        }

        $writeText(17, 0, 530, 200, $headerColor_so, $this->fontPathhh, "الجوال");
        imagettftext($image, 17, 0, 430, 240, $textColor, $this->fontPath, $transferData->recipient_mobile );
        $writeText(16, 0, 350, 200, $headerColor_so, $this->fontPathhh, "رقم الإشعار");
        imagettftext($image, 17, 0, 220, 240, $headerColor, $this->fontPath, $transferData->movement_number);

        $writeText(17, 0, 150, 200, $headerColor_so, $this->fontPathhh, "رقم السري");
        imagettftext($image, 17, 0, 50, 240, $headerColor_soo, $this->fontPath, $transferData->password);

        $writeText(17, 0, 770, 300, $headerColor_so, $this->fontPathhh, "عنوان");
        // تقسيم عنوان المستخدم إلى أسطر إذا كان طويلاً
        $maxWidthAddress = 800; // عرض السطر الذي تريده لعنوان المستخدم
        $addressLines = $this->splitTextIntoLines($userAddress, $maxWidthAddress, $this->fontPath, 14);
        $yAddress = 340; // نقطة البداية الرأسية للنص
        $lineHeightAddress = 30; // المسافة بين الأسطر
        foreach ($addressLines as $line) {
            $writeText(14, 0, 770, $yAddress, $textColor, $this->fontPathhhh, $line);
            $yAddress += $lineHeightAddress;
        }

        $number = (int)$transferData->received_amount;
        $numberStr = (string)$number;
        $currencyStr = $transferData->receivedCurrency ? $transferData->receivedCurrency->name_ar : '';  // جلب اسم العملة بالعربية

        // حساب أبعاد الرقم
        $bboxNumber = imagettfbbox(17, 0, $this->fontPath, $numberStr);
        $numberWidth = abs($bboxNumber[2] - $bboxNumber[0]);

        // حساب أبعاد العملة
        $bboxCurrency = imagettfbbox(17, 0, $this->fontPath, $currencyStr);
        $currencyWidth = abs($bboxCurrency[2] - $bboxCurrency[0]);

        // تحديد هامش بين العنصرين (مثلاً 10 بكسل)
        $margin = 0;

        // إجمالي عرض المجموعة (الرقم + المسافة بينهما + العملة)
        $totalWidth = $numberWidth + $margin + $currencyWidth;

        // احتساب نقطة البداية بحيث يكون المجموع في منتصف الشاشة
        $xStart = ($width - $totalWidth) / 2;

        // كتابة العملة على يسار الرقم (نبدأ بكتابة العملة أولاً ثم الرقم بعده)
        $writeText(17, 0, 400, 490, $highlightColor, $this->fontPath, $currencyStr);

        imagettftext($image, 17, 0, 410, 490, $highlightColor, $this->fontPath, $numberStr);

        $textNumberRaw = $this->arabic->int2str($number);
        $textNumber = $this->arabic->utf8Glyphs($textNumberRaw, true);

        // حساب أبعاد النص بعد معالجته
        $bbox = imagettfbbox(17, 0, $this->fontPath, $textNumber);
        $textWidth = abs($bbox[2] - $bbox[0]);

        // احتساب نقطة البداية لجعل النص في منتصف الشاشة (بافتراض أن عرض الصورة مخزن في $width)
        $xCenter = ($width - $textWidth) / 2;

        // كتابة النص في منتصف الشاشة
        imagettftext($image, 17, 0, $xCenter, 550, $textColor, $this->fontPath, $textNumber);

        // تحويل الصورة إلى PNG وتخزينها في ذاكرة مؤقتة
        ob_start();
        imagepng($image);
        $imageContent = ob_get_clean();
        imagedestroy($image);

        // إرجاع الصورة بصيغة Base64
        return base64_encode($imageContent);
    }
}
