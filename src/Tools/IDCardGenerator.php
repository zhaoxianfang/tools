<?php

namespace zxf\Tools;

use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;

/**
 * 中国身份证号码生成与验证工具类
 *
 * 功能特点：
 * - 支持15位/18位身份证生成
 * - 支持自定义性别参数（m/male/f/female）
 * - 支持批量生成N个身份证
 * - 精确的行政区划处理
 * - 完整的生日、性别、年龄等信息解析
 * - 生肖、星座、世代计算
 * - 严格的输入验证
 */

/**
 * 使用示例：
 * 1、生成单个身份证号码：
 * $idCard = IDCardGenerator::generate(); // 随机生成
 * $idCard = IDCardGenerator::generate(['province' => '广东省']); // 生成指定省份的身份证
 * $idCard = IDCardGenerator::generate(['gender' => 'm']); // 指定男性 （支持m/male/f/female）
 * $idCard = IDCardGenerator::generate(['birthday' => '1990-08-15']); // 生成指定出生日期的身份证
 * $idCard = IDCardGenerator::generate(['length' => 15]); // 生成15位身份证
 * $idCard = IDCardGenerator::generate([
 *              'province' => '江苏省', // 省份
 *              'birthday' => '1985-05-20', // 生日
 *              'gender' => 'f', //  性别 （支持m/male/f/female）
 *              'length' => 18  // 18位身份证
 *           ]);
 *
 * 2、批量生成多个身份证号码：
 * $idCards = IDCardGenerator::generateBatch(10);
 * $idCards = IDCardGenerator::generateBatch(5, ['province' => '广东省']); // 批量生成指定省份的身份证
 * $idCards = IDCardGenerator::generateBatch(5, ['length' => 15]); // 批量生成15位身份证
 * $idCards =IDCardGenerator::generateBatch(3, [ // 批量生成3个1990年出生的男性身份证
 *              'birthday' => '1990-01-01',
 *              'gender' => 'male'
 *          ]);
 *
 * 3、验证身份证号码：
 * $isValid = IDCardGenerator::validate('11010519491231002X'); // 验证身份证 返回bool 是否有效
 *
 * 4、解析身份证信息：
 * $info = IDCardGenerator::parse('11010519491231002X'); // 返回身份证信息数组
 *
 * 5、15位身份证升级为18位
 * $idCard18 = IDCardGenerator::upgradeTo18('110105491231002');
 *
 * 6. 获取支持的省份列表
 * $provinces = IDCardGenerator::getProvinces();
 */
final class IDCardGenerator
{
    // 省份代码映射
    private const PROVINCE_CODES = [
        11 => '北京市', 12 => '天津市', 13 => '河北省', 14 => '山西省', 15 => '内蒙古自治区',
        21 => '辽宁省', 22 => '吉林省', 23 => '黑龙江省',
        31 => '上海市', 32 => '江苏省', 33 => '浙江省', 34 => '安徽省', 35 => '福建省',
        36 => '江西省', 37 => '山东省',
        41 => '河南省', 42 => '湖北省', 43 => '湖南省', 44 => '广东省', 45 => '广西壮族自治区',
        46 => '海南省',
        50 => '重庆市', 51 => '四川省', 52 => '贵州省', 53 => '云南省', 54 => '西藏自治区',
        61 => '陕西省', 62 => '甘肃省', 63 => '青海省', 64 => '宁夏回族自治区', 65 => '新疆维吾尔自治区',
        71 => '台湾省', 81 => '香港特别行政区', 82 => '澳门特别行政区',
    ];

    // 校验码权重因子
    private const CHECKSUM_WEIGHTS = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];

    // 校验码对应值
    private const CHECKSUM_CODES = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];

    // 直辖市和特别行政区代码
    private const MUNICIPALITIES = [11, 12, 31, 50, 81, 82];

    // 生肖列表
    private const ZODIAC_SIGNS = ['猴', '鸡', '狗', '猪', '鼠', '牛', '虎', '兔', '龙', '蛇', '马', '羊'];

    // 星座日期范围
    private const CONSTELLATION_RANGES = [
        ['name' => '水瓶座', 'start' => '01-20', 'end' => '02-18'],
        ['name' => '双鱼座', 'start' => '02-19', 'end' => '03-20'],
        ['name' => '白羊座', 'start' => '03-21', 'end' => '04-19'],
        ['name' => '金牛座', 'start' => '04-20', 'end' => '05-20'],
        ['name' => '双子座', 'start' => '05-21', 'end' => '06-21'],
        ['name' => '巨蟹座', 'start' => '06-22', 'end' => '07-22'],
        ['name' => '狮子座', 'start' => '07-23', 'end' => '08-22'],
        ['name' => '处女座', 'start' => '08-23', 'end' => '09-22'],
        ['name' => '天秤座', 'start' => '09-23', 'end' => '10-23'],
        ['name' => '天蝎座', 'start' => '10-24', 'end' => '11-22'],
        ['name' => '射手座', 'start' => '11-23', 'end' => '12-21'],
        ['name' => '摩羯座', 'start' => '12-22', 'end' => '01-19'],
    ];

    // 年龄限制范围
    private const MIN_AGE = 0;

    private const MAX_AGE = 125;

    /**
     * 生成单个身份证号码
     *
     * @param array{
     *     province?: string|null,
     *     birthday?: string|DateTimeInterface|null,
     *     gender?: 'm'|'male'|'f'|'female'|null,
     *     length?: 15|18
     * } $options 生成选项
     * @return string 身份证号码
     *
     * @throws InvalidArgumentException 当参数无效时抛出
     */
    public static function generate(array $options = []): string
    {
        $options = array_merge([
            'province' => null,
            'birthday' => null,
            'gender' => null,
            'length' => 18,
        ], $options);

        self::validateOptions($options);

        $areaCode = self::generateAreaCode($options['province']);
        $birthCode = self::generateBirthCode($options['birthday'], $options['length'] === 18);
        $sequenceCode = self::generateSequenceCode($options['gender']);

        if ($options['length'] === 15) {
            return $areaCode.$birthCode.$sequenceCode;
        }

        $idBase = $areaCode.$birthCode.$sequenceCode;

        return $idBase.self::calculateChecksum($idBase);
    }

    /**
     * 批量生成身份证号码
     *
     * @param  int  $count  要生成的数量
     * @param array{
     *     province?: string|null,
     *     birthday?: string|DateTimeInterface|null,
     *     gender?: 'm'|'male'|'f'|'female'|null,
     *     length?: 15|18
     * } $options 生成选项
     * @return array<string> 生成的身份证号码数组
     *
     * @throws InvalidArgumentException 当参数无效时抛出
     */
    public static function generateBatch(int $count, array $options = []): array
    {
        if ($count <= 0) {
            throw new InvalidArgumentException('生成数量必须大于0');
        }

        $ids = [];
        for ($i = 0; $i < $count; $i++) {
            $ids[] = self::generate($options);
        }

        return $ids;
    }

    /**
     * 验证生成选项
     */
    private static function validateOptions(array $options): void
    {
        if (! in_array($options['length'], [15, 18], true)) {
            throw new InvalidArgumentException('身份证位数必须是15或18');
        }

        if ($options['gender'] !== null) {
            $gender = strtolower($options['gender']);
            if (! in_array($gender, ['m', 'male', 'f', 'female'], true)) {
                throw new InvalidArgumentException("性别必须是'm/male'或'f/female'");
            }
        }

        if ($options['birthday'] !== null) {
            if (is_string($options['birthday'])) {
                $date = DateTimeImmutable::createFromFormat('Y-m-d', $options['birthday']);
                if ($date === false || $date->format('Y-m-d') !== $options['birthday']) {
                    throw new InvalidArgumentException('出生日期格式无效，请使用YYYY-MM-DD格式');
                }
            } elseif (! ($options['birthday'] instanceof DateTimeInterface)) {
                throw new InvalidArgumentException('出生日期必须是字符串或DateTimeInterface实例');
            }
        }
    }

    /**
     * 生成地区编码
     */
    private static function generateAreaCode(?string $province): string
    {
        $provinceCode = $province ? self::resolveProvinceCode($province) : array_rand(self::PROVINCE_CODES);
        $isMunicipality = in_array($provinceCode, self::MUNICIPALITIES, true);

        $cityMax = $isMunicipality ? 1 : 99;
        $districtMax = $isMunicipality ? 20 : 99;

        $cityCode = str_pad((string) random_int(1, $cityMax), 2, '0', STR_PAD_LEFT);
        $districtCode = str_pad((string) random_int(1, $districtMax), 2, '0', STR_PAD_LEFT);

        return $provinceCode.$cityCode.$districtCode;
    }

    /**
     * 解析省份名称到代码
     */
    private static function resolveProvinceCode(string $province): int
    {
        $normalizedProvince = str_replace(['省', '市', '自治区', '特别行政区', '壮族', '回族', '维吾尔'], '', $province);

        foreach (self::PROVINCE_CODES as $code => $name) {
            $normalizedName = str_replace(['省', '市', '自治区', '特别行政区', '壮族', '回族', '维吾尔'], '', $name);
            if ($normalizedName === $normalizedProvince) {
                return $code;
            }
        }

        throw new InvalidArgumentException("无效的省份名称: {$province}");
    }

    /**
     * 生成出生日期编码
     */
    private static function generateBirthCode(null|string|DateTimeInterface $birthday, bool $is18Digit): string
    {
        if ($birthday !== null) {
            $date = $birthday instanceof DateTimeInterface
                ? DateTimeImmutable::createFromInterface($birthday)
                : DateTimeImmutable::createFromFormat('Y-m-d', $birthday);

            return $is18Digit
                ? $date->format('Ymd')
                : $date->format('ymd');
        }

        return self::generateRandomBirthCode($is18Digit);
    }

    /**
     * 生成随机出生日期编码
     */
    private static function generateRandomBirthCode(bool $is18Digit): string
    {
        $currentYear = (int) date('Y');
        $year = random_int($currentYear - self::MAX_AGE, $currentYear - self::MIN_AGE);
        $month = random_int(1, 12);
        $day = random_int(1, self::daysInMonth($year, $month));

        return $is18Digit
            ? sprintf('%04d%02d%02d', $year, $month, $day)
            : sprintf('%02d%02d%02d', $year % 100, $month, $day);
    }

    /**
     * 计算某年某月的天数
     */
    private static function daysInMonth(int $year, int $month): int
    {
        return $month === 2
            ? (self::isLeapYear($year) ? 29 : 28)
            : (($month === 4 || $month === 6 || $month === 9 || $month === 11) ? 30 : 31);
    }

    /**
     * 判断闰年
     */
    private static function isLeapYear(int $year): bool
    {
        return ($year % 4 === 0 && $year % 100 !== 0) || ($year % 400 === 0);
    }

    /**
     * 生成顺序码（包含性别信息）
     */
    private static function generateSequenceCode(?string $gender): string
    {
        $sequence = random_int(1, 999);

        if ($gender !== null) {
            $gender = strtolower($gender);
            $shouldBeOdd = in_array($gender, ['m', 'male'], true);
            $isOdd = $sequence % 2 === 1;

            if ($shouldBeOdd !== $isOdd) {
                $sequence = min(999, $sequence + 1);
            }
        }

        return str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * 计算校验码
     */
    private static function calculateChecksum(string $idBase): string
    {
        if (strlen($idBase) !== 17) {
            throw new InvalidArgumentException('身份证前17位长度必须为17');
        }

        $sum = 0;
        for ($i = 0; $i < 17; $i++) {
            if (! ctype_digit($idBase[$i])) {
                throw new InvalidArgumentException('身份证前17位必须为数字');
            }
            $sum += (int) $idBase[$i] * self::CHECKSUM_WEIGHTS[$i];
        }

        return self::CHECKSUM_CODES[$sum % 11];
    }

    /**
     * 验证身份证号码有效性
     */
    public static function validate(string $idCard): bool
    {
        $length = strlen($idCard);

        return match ($length) {
            15 => self::validate15Digit($idCard),
            18 => self::validate18Digit($idCard),
            default => false
        };
    }

    /**
     * 验证15位身份证
     */
    private static function validate15Digit(string $idCard): bool
    {
        if (! ctype_digit($idCard)) {
            return false;
        }

        $provinceCode = (int) substr($idCard, 0, 2);
        if (! isset(self::PROVINCE_CODES[$provinceCode])) {
            return false;
        }

        $year = substr($idCard, 6, 2);
        $month = substr($idCard, 8, 2);
        $day = substr($idCard, 10, 2);

        return self::isValidDate($year, $month, $day, false);
    }

    /**
     * 验证18位身份证
     */
    private static function validate18Digit(string $idCard): bool
    {
        $base = substr($idCard, 0, 17);
        if (! ctype_digit($base)) {
            return false;
        }

        $provinceCode = (int) substr($idCard, 0, 2);
        if (! isset(self::PROVINCE_CODES[$provinceCode])) {
            return false;
        }

        $year = substr($idCard, 6, 4);
        $month = substr($idCard, 10, 2);
        $day = substr($idCard, 12, 2);

        if (! self::isValidDate($year, $month, $day, true)) {
            return false;
        }

        return strtoupper($idCard[17]) === self::calculateChecksum($base);
    }

    /**
     * 验证日期有效性
     */
    private static function isValidDate(string $year, string $month, string $day, bool $fullYear): bool
    {
        $monthInt = (int) $month;
        $dayInt = (int) $day;

        if ($monthInt < 1 || $monthInt > 12) {
            return false;
        }

        $yearInt = (int) $year;
        if (! $fullYear) {
            $yearInt += ($yearInt < 50) ? 2000 : 1900;
        }

        return $dayInt >= 1 && $dayInt <= self::daysInMonth($yearInt, $monthInt);
    }

    /**
     * 解析身份证信息
     *
     * @return array{
     *     is_18_digit: bool,
     *     area_code: string,
     *     province: string,
     *     birthday: string,
     *     age: int,
     *     gender: 'male'|'female',
     *     sequence_code: string,
     *     check_code: string|null,
     *     zodiac: string,
     *     constellation: string,
     *     generation: string
     * }
     *
     * @throws InvalidArgumentException 当身份证无效时抛出
     */
    public static function parse(string $idCard): array
    {
        if (! self::validate($idCard)) {
            throw new InvalidArgumentException('无效的身份证号码');
        }

        $is18Digit = strlen($idCard) === 18;
        $provinceCode = (int) substr($idCard, 0, 2);
        $sequence = substr($idCard, $is18Digit ? 14 : 12, 3);
        $birthday = self::parseBirthday($idCard, $is18Digit);
        $birthDate = DateTimeImmutable::createFromFormat('Y-m-d', $birthday);

        return [
            'is_18_digit' => $is18Digit,
            'area_code' => substr($idCard, 0, 6),
            'province' => self::PROVINCE_CODES[$provinceCode] ?? '未知',
            'birthday' => $birthday,
            'age' => self::calculateAge($birthDate),
            'gender' => ((int) $sequence % 2 === 0) ? 'female' : 'male',
            'sequence_code' => $sequence,
            'check_code' => $is18Digit ? strtoupper($idCard[17]) : null,
            'zodiac' => self::getZodiacSign($birthDate),
            'constellation' => self::getConstellation($birthDate),
            'generation' => self::getGeneration($birthDate),
        ];
    }

    /**
     * 解析出生日期
     */
    private static function parseBirthday(string $idCard, bool $is18Digit): string
    {
        if ($is18Digit) {
            $birthday = substr($idCard, 6, 8);

            return sprintf('%s-%s-%s', substr($birthday, 0, 4), substr($birthday, 4, 2), substr($birthday, 6, 2));
        }

        $birthday = substr($idCard, 6, 6);
        $century = (int) substr($birthday, 0, 2) < 50 ? '20' : '19';

        return sprintf('%s%s-%s-%s', $century, substr($birthday, 0, 2), substr($birthday, 2, 2), substr($birthday, 4, 2));
    }

    /**
     * 计算年龄
     */
    private static function calculateAge(DateTimeImmutable $birthDate): int
    {
        $now = new DateTimeImmutable;
        $age = $now->diff($birthDate)->y;

        return max(self::MIN_AGE, min($age, self::MAX_AGE));
    }

    /**
     * 获取生肖
     */
    private static function getZodiacSign(DateTimeImmutable $birthDate): string
    {
        return self::ZODIAC_SIGNS[$birthDate->format('Y') % 12];
    }

    /**
     * 获取星座
     */
    private static function getConstellation(DateTimeImmutable $birthDate): string
    {
        $monthDay = $birthDate->format('m-d');

        foreach (self::CONSTELLATION_RANGES as $range) {
            if ($monthDay >= $range['start'] && $monthDay <= $range['end']) {
                return $range['name'];
            }
        }

        return '摩羯座';
    }

    /**
     * 获取世代
     */
    private static function getGeneration(DateTimeImmutable $birthDate): string
    {
        $year = (int) $birthDate->format('Y');

        if ($year >= 2010) {
            return 'Z世代';
        }
        if ($year >= 1995) {
            return '千禧一代';
        }
        if ($year >= 1980) {
            return 'Y世代';
        }
        if ($year >= 1965) {
            return 'X世代';
        }
        if ($year >= 1946) {
            return '婴儿潮一代';
        }

        return '传统一代';
    }

    /**
     * 将15位身份证升级为18位
     */
    public static function upgradeTo18(string $idCard15): string
    {
        if (strlen($idCard15) !== 15 || ! self::validate($idCard15)) {
            throw new InvalidArgumentException('无效的15位身份证号码');
        }

        $areaCode = substr($idCard15, 0, 6);
        $birthYear = substr($idCard15, 6, 2);
        $century = (int) $birthYear < 50 ? '20' : '19';
        $birthCode = $century.$birthYear.substr($idCard15, 8, 4);
        $sequenceCode = substr($idCard15, 12, 3);
        $idBase = $areaCode.$birthCode.$sequenceCode;

        return $idBase.self::calculateChecksum($idBase);
    }

    /**
     * 获取所有支持的省份列表
     */
    public static function getProvinces(): array
    {
        return self::PROVINCE_CODES;
    }
}
