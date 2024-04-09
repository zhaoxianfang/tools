<?php

namespace zxf\Tools;

/**
 * 国家区号、手机号码前缀
 */
class CountryNumber
{
    private static self $instance;

    // 国家列表
    private array $countries = [
        'Abkhazia'                                     => [
            'cn_name' => '阿布哈兹',
            'us_name' => 'Abkhazia',
            'number'  => '7', // 国家区号，可能多个国家共用一个区号，例如：美国、加拿大、百慕大、巴哈马等国家和地区共用区号1
            'prefix'  => ['940'], // 手机号码前缀
        ],
        'Afghanistan'                                  => [
            'cn_name' => '阿富汗',
            'us_name' => 'Afghanistan',
            'number'  => '93',
            'prefix'  => ['70', '71', '72', '73', '74', '75', '76', '77', '78', '79'],
        ],
        'Aland'                                        => [
            'cn_name' => '奥兰群岛',
            'us_name' => 'Aland',
            'number'  => '358',
            'prefix'  => ['18', '40'],
        ],
        'Albania'                                      => [
            'cn_name' => '阿尔巴尼亚',
            'us_name' => 'Albania',
            'number'  => '355',
            'prefix'  => ['66', '67'],
        ],
        'Algeria'                                      => [
            'cn_name' => '阿尔及利亚',
            'us_name' => 'Algeria',
            'number'  => '213',
            'prefix'  => ['5', '6', '7'],
        ],
        'American-Samoa'                               => [
            'cn_name' => '美属萨摩亚',
            'us_name' => 'American-Samoa',
            'number'  => '1',
            'prefix'  => ['684'],
        ],
        'Andorra'                                      => [
            'cn_name' => '安道尔',
            'us_name' => 'Andorra',
            'number'  => '376',
            'prefix'  => ['3', '4', '5'],
        ],
        'Angola'                                       => [
            'cn_name' => '安哥拉',
            'us_name' => 'Angola',
            'number'  => '244',
            'prefix'  => ['92', '93', '94', '95', '96', '97'],
        ],
        'Anguilla'                                     => [
            'cn_name' => '安圭拉',
            'us_name' => 'Anguilla',
            'number'  => '1',
            'prefix'  => ['264'],
        ],
        'Antarctica'                                   => [
            'cn_name' => '南极洲',
            'us_name' => 'Antarctica',
            'number'  => '672',
            'prefix'  => [''],
        ],
        'Antigua-and-Barbuda'                          => [
            'cn_name' => '安提瓜和巴布达',
            'us_name' => 'Antigua-and-Barbuda',
            'number'  => '1',
            'prefix'  => ['268'],
        ],
        'Argentina'                                    => [
            'cn_name' => '阿根廷',
            'us_name' => 'Argentina',
            'number'  => '54',
            'prefix'  => ['9'],
        ],
        'Armenia'                                      => [
            'cn_name' => '亚美尼亚',
            'us_name' => 'Armenia',
            'number'  => '374',
            'prefix'  => ['91', '94', '95'],
        ],
        'Aruba'                                        => [
            'cn_name' => '阿鲁巴',
            'us_name' => 'Aruba',
            'number'  => '297',
            'prefix'  => ['5', '6', '7', '9'],
        ],
        'Australia'                                    => [
            'cn_name' => '澳大利亚',
            'us_name' => 'Australia',
            'number'  => '61',
            'prefix'  => ['4'],
        ],
        'Austria'                                      => [
            'cn_name' => '奥地利',
            'us_name' => 'Austria',
            'number'  => '43',
            'prefix'  => ['6', '65', '66', '676'],
        ],
        'Azerbaijan'                                   => [
            'cn_name' => '阿塞拜疆',
            'us_name' => 'Azerbaijan',
            'number'  => '994',
            'prefix'  => ['40', '50', '51', '55', '60', '70', '77'],
        ],
        'Bahamas'                                      => [
            'cn_name' => '巴哈马',
            'us_name' => 'Bahamas',
            'number'  => '1',
            'prefix'  => ['242'],
        ],
        'Bahrain'                                      => [
            'cn_name' => '巴林',
            'us_name' => 'Bahrain',
            'number'  => '973',
            'prefix'  => ['3', '6', '7', '8', '9'],
        ],
        'Bangladesh'                                   => [
            'cn_name' => '孟加拉国',
            'us_name' => 'Bangladesh',
            'number'  => '880',
            'prefix'  => ['10', '11', '12', '13', '14', '15', '16', '17', '18', '19'],
        ],
        'Barbados'                                     => [
            'cn_name' => '巴巴多斯',
            'us_name' => 'Barbados',
            'number'  => '1',
            'prefix'  => ['246'],
        ],
        'Basque-Country'                               => [
            'cn_name' => '巴斯克',
            'us_name' => 'Basque-Country',
            'number'  => '34',
            'prefix'  => [''],
        ],
        'Belarus'                                      => [
            'cn_name' => '白俄罗斯',
            'us_name' => 'Belarus',
            'number'  => '375',
            'prefix'  => ['25', '29', '33', '44'],
        ],
        'Belgium'                                      => [
            'cn_name' => '比利时',
            'us_name' => 'Belgium',
            'number'  => '32',
            'prefix'  => ['4', '477', '478', '479', '48', '49', '50', '51', '52', '53', '54', '55', '56', '57', '58', '59', '477'],
        ],
        'Belize'                                       => [
            'cn_name' => '伯利兹',
            'us_name' => 'Belize',
            'number'  => '501',
            'prefix'  => ['6'],
        ],
        'Benin'                                        => [
            'cn_name' => '贝宁',
            'us_name' => 'Benin',
            'number'  => '229',
            'prefix'  => ['9'],
        ],
        'Bermuda'                                      => [
            'cn_name' => '百慕大',
            'us_name' => 'Bermuda',
            'number'  => '1',
            'prefix'  => ['441'],
        ],
        'Bhutan'                                       => [
            'cn_name' => '不丹',
            'us_name' => 'Bhutan',
            'number'  => '975',
            'prefix'  => ['17', '18', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33', '34', '35', '36', '37', '38', '39'],
        ],
        'Bolivia'                                      => [
            'cn_name' => '玻利维亚',
            'us_name' => 'Bolivia',
            'number'  => '591',
            'prefix'  => ['6', '7'],
        ],
        'Bosnia-and-Herzegovina'                       => [
            'cn_name' => '波斯尼亚和黑塞哥维那',
            'us_name' => 'Bosnia-and-Herzegovina',
            'number'  => '387',
            'prefix'  => ['60', '61', '62', '63', '64', '65', '66', '67', '68', '69'],
        ],
        'Botswana'                                     => [
            'cn_name' => '博茨瓦纳',
            'us_name' => 'Botswana',
            'number'  => '267',
            'prefix'  => ['71', '72', '73', '74', '75'],
        ],
        'Brazil'                                       => [
            'cn_name' => '巴西',
            'us_name' => 'Brazil',
            'number'  => '55',
            'prefix'  => ['10', '11', '12', '13', '14', '15', '16', '17', '18', '19'],
        ],
        'British-Antarctic-Territory'                  => [
            'cn_name' => '英属南极领地',
            'us_name' => 'British-Antarctic-Territory',
            'number'  => '',
            'prefix'  => [''],
        ],
        'British-Virgin-Islands'                       => [
            'cn_name' => '英属维尔京群岛',
            'us_name' => 'British-Virgin-Islands',
            'number'  => '1',
            'prefix'  => ['284'],
        ],
        'Brunei'                                       => [
            'cn_name' => '文莱',
            'us_name' => 'Brunei',
            'number'  => '673',
            'prefix'  => ['2', '3', '7', '8', '9'],
        ],
        'Bulgaria'                                     => [
            'cn_name' => '保加利亚',
            'us_name' => 'Bulgaria',
            'number'  => '359',
            'prefix'  => ['87', '88', '89'],
        ],
        'Burkina-Faso'                                 => [
            'cn_name' => '布基纳法索',
            'us_name' => 'Burkina-Faso',
            'number'  => '226',
            'prefix'  => ['6', '7', '8'],
        ],
        'Burundi'                                      => [
            'cn_name' => '布隆迪',
            'us_name' => 'Burundi',
            'number'  => '257',
            'prefix'  => ['6'],
        ],
        'Cambodia'                                     => [
            'cn_name' => '柬埔寨',
            'us_name' => 'Cambodia',
            'number'  => '855',
            'prefix'  => ['1', '6', '7', '8', '9'],
        ],
        'Cameroon'                                     => [
            'cn_name' => '喀麦隆',
            'us_name' => 'Cameroon',
            'number'  => '237',
            'prefix'  => ['2', '6', '6', '7', '9'],
        ],
        'Canada'                                       => [
            'cn_name' => '加拿大',
            'us_name' => 'Canada',
            'number'  => '1',
            'prefix'  => ['204', '226', '236', '249', '250', '289', '306', '343', '365', '367', '403', '416', '418', '431', '437', '438', '450', '506', '514', '519', '548', '579', '581', '587', '604', '613', '639', '647', '672', '705', '709', '742', '778', '780', '782', '807', '819', '825', '867', '873', '878', '902', '905'],
        ],
        'Canary-Islands'                               => [
            'cn_name' => '加那利群岛',
            'us_name' => 'Canary-Islands',
            'number'  => '34',
            'prefix'  => [''],
        ],
        'Cape-Verde'                                   => [
            'cn_name' => '佛得角',
            'us_name' => 'Cape-Verde',
            'number'  => '238',
            'prefix'  => ['5'],
        ],
        'Cayman-Islands'                               => [
            'cn_name' => '开曼群岛',
            'us_name' => 'Cayman-Islands',
            'number'  => '1',
            'prefix'  => ['345'],
        ],
        'Central-African-Republic'                     => [
            'cn_name' => '中非共和国',
            'us_name' => 'Central-African-Republic',
            'number'  => '236',
            'prefix'  => ['7', '8', '9'],
        ],
        'Chad'                                         => [
            'cn_name' => '乍得',
            'us_name' => 'Chad',
            'number'  => '235',
            'prefix'  => ['6', '7', '9'],
        ],
        'Chile'                                        => [
            'cn_name' => '智利',
            'us_name' => 'Chile',
            'number'  => '56',
            'prefix'  => ['2', '3', '4', '5', '6', '7', '8', '9'],
        ],
        'China'                                        => [
            'cn_name' => '中国',
            'us_name' => 'China',
            'number'  => '86',
            'prefix'  => ['13', '14', '15', '16', '17', '18', '19'],
        ],
        'Christmas-Island'                             => [
            'cn_name' => '圣诞岛',
            'us_name' => 'Christmas-Island',
            'number'  => '61',
            'prefix'  => [''],
        ],
        'Cocos-Keeling-Islands'                        => [
            'cn_name' => '科科斯（基林）群岛',
            'us_name' => 'Cocos-Keeling-Islands',
            'number'  => '61',
            'prefix'  => [''],
        ],
        'Colombia'                                     => [
            'cn_name' => '哥伦比亚',
            'us_name' => 'Colombia',
            'number'  => '57',
            'prefix'  => ['3', '3'],
        ],
        'Commonwealth'                                 => [
            'cn_name' => '英联邦',
            'us_name' => 'Commonwealth',
            'number'  => '',
            'prefix'  => [''],
        ],
        'Comoros'                                      => [
            'cn_name' => '科摩罗',
            'us_name' => 'Comoros',
            'number'  => '269',
            'prefix'  => ['3', '7'],
        ],
        'Cook-Islands'                                 => [
            'cn_name' => '库克群岛',
            'us_name' => 'Cook-Islands',
            'number'  => '682',
            'prefix'  => ['5'],
        ],
        'Costa-Rica'                                   => [
            'cn_name' => '哥斯达黎加',
            'us_name' => 'Costa-Rica',
            'number'  => '506',
            'prefix'  => ['5', '6', '7', '8', '9'],
        ],
        'Cote-dIvoire'                                 => [
            'cn_name' => '科特迪瓦',
            'us_name' => 'Cote-dIvoire',
            'number'  => '225',
            'prefix'  => ['01', '02', '03', '04', '05', '06', '07', '08', '09'],
        ],
        'Croatia'                                      => [
            'cn_name' => '克罗地亚',
            'us_name' => 'Croatia',
            'number'  => '385',
            'prefix'  => ['9'],
        ],
        'Cuba'                                         => [
            'cn_name' => '古巴',
            'us_name' => 'Cuba',
            'number'  => '53',
            'prefix'  => ['5'],
        ],
        'Curacao'                                      => [
            'cn_name' => '库拉索',
            'us_name' => 'Curacao',
            'number'  => '599',
            'prefix'  => ['9'],
        ],
        'Cyprus'                                       => [
            'cn_name' => '塞浦路斯',
            'us_name' => 'Cyprus',
            'number'  => '357',
            'prefix'  => ['96', '97', '99'],
        ],
        'Czech-Republic'                               => [
            'cn_name' => '捷克共和国',
            'us_name' => 'Czech-Republic',
            'number'  => '420',
            'prefix'  => ['60', '601', '602', '603', '604', '605', '606', '607', '608', '609', '72', '73', '77', '778', '79', '910', '911', '912', '913', '914', '915', '916', '917', '918', '919', '72', '73', '77', '778', '79', '910', '911', '912', '913', '914', '915', '916', '917', '918', '919'],
        ],
        'Democratic-Republic-of-the-Congo'             => [
            'cn_name' => '刚果民主共和国',
            'us_name' => 'Democratic-Republic-of-the-Congo',
            'number'  => '243',
            'prefix'  => ['8', '99'],
        ],
        'Denmark'                                      => [
            'cn_name' => '丹麦',
            'us_name' => 'Denmark',
            'number'  => '45',
            'prefix'  => ['2', '3', '4', '5', '6', '7', '8'],
        ],
        'Djibouti'                                     => [
            'cn_name' => '吉布提',
            'us_name' => 'Djibouti',
            'number'  => '253',
            'prefix'  => ['77', '78', '79'],
        ],
        'Dominica'                                     => [
            'cn_name' => '多米尼加',
            'us_name' => 'Dominica',
            'number'  => '1',
            'prefix'  => ['767'],
        ],
        'Dominican-Republic'                           => [
            'cn_name' => '多米尼加共和国',
            'us_name' => 'Dominican-Republic',
            'number'  => '1',
            'prefix'  => ['809', '829', '849'],
        ],
        'East-Timor'                                   => [
            'cn_name' => '东帝汶',
            'us_name' => 'East-Timor',
            'number'  => '670',
            'prefix'  => [''],
        ],
        'Ecuador'                                      => [
            'cn_name' => '厄瓜多尔',
            'us_name' => 'Ecuador',
            'number'  => '593',
            'prefix'  => ['8', '9'],
        ],
        'Egypt'                                        => [
            'cn_name' => '埃及',
            'us_name' => 'Egypt',
            'number'  => '20',
            'prefix'  => ['10', '11', '12', '15'],
        ],
        'El-Salvador'                                  => [
            'cn_name' => '萨尔瓦多',
            'us_name' => 'El-Salvador',
            'number'  => '503',
            'prefix'  => ['6'],
        ],
        'England'                                      => [
            'cn_name' => '英格兰',
            'us_name' => 'England',
            'number'  => '44',
            'prefix'  => [''],
        ],
        'Equatorial-Guinea'                            => [
            'cn_name' => '赤道几内亚',
            'us_name' => 'Equatorial-Guinea',
            'number'  => '240',
            'prefix'  => ['222'],
        ],
        'Eritrea'                                      => [
            'cn_name' => '厄立特里亚',
            'us_name' => 'Eritrea',
            'number'  => '291',
            'prefix'  => ['1'],
        ],
        'Estonia'                                      => [
            'cn_name' => '爱沙尼亚',
            'us_name' => 'Estonia',
            'number'  => '372',
            'prefix'  => ['5', '81', '82'],
        ],
        'Ethiopia'                                     => [
            'cn_name' => '埃塞俄比亚',
            'us_name' => 'Ethiopia',
            'number'  => '251',
            'prefix'  => ['9'],
        ],
        'European-Union'                               => [
            'cn_name' => '欧盟',
            'us_name' => 'European-Union',
            'number'  => '',
            'prefix'  => [''],
        ],
        'Falkland-Islands'                             => [
            'cn_name' => '福克兰群岛',
            'us_name' => 'Falkland-Islands',
            'number'  => '500',
            'prefix'  => [''],
        ],
        'Faroes'                                       => [
            'cn_name' => '法罗群岛',
            'us_name' => 'Faroes',
            'number'  => '298',
            'prefix'  => [''],
        ],
        'Fiji'                                         => [
            'cn_name' => '斐济',
            'us_name' => 'Fiji',
            'number'  => '679',
            'prefix'  => ['7', '8', '9'],
        ],
        'Finland'                                      => [
            'cn_name' => '芬兰',
            'us_name' => 'Finland',
            'number'  => '358',
            'prefix'  => ['4', '5', '50', '51', '52', '53', '54', '55', '56', '57', '58'],
        ],
        'France'                                       => [
            'cn_name' => '法国',
            'us_name' => 'France',
            'number'  => '33',
            'prefix'  => ['6'],
        ],
        'French-Polynesia'                             => [
            'cn_name' => '法属波利尼西亚',
            'us_name' => 'French-Polynesia',
            'number'  => '689',
            'prefix'  => [''],
        ],
        'French-Southern-Territories'                  => [
            'cn_name' => '法属南部领地',
            'us_name' => 'French-Southern-Territories',
            'number'  => '262',
            'prefix'  => [''],
        ],
        'Gabon'                                        => [
            'cn_name' => '加蓬',
            'us_name' => 'Gabon',
            'number'  => '241',
            'prefix'  => ['05'],
        ],
        'Gambia'                                       => [
            'cn_name' => '冈比亚',
            'us_name' => 'Gambia',
            'number'  => '220',
            'prefix'  => ['7', '9'],
        ],
        'Georgia'                                      => [
            'cn_name' => '格鲁吉亚',
            'us_name' => 'Georgia',
            'number'  => '995',
            'prefix'  => ['5', '7', '8'],
        ],
        'Germany'                                      => [
            'cn_name' => '德国',
            'us_name' => 'Germany',
            'number'  => '49',
            'prefix'  => ['151', '157', '159'],
        ],
        'Ghana'                                        => [
            'cn_name' => '加纳',
            'us_name' => 'Ghana',
            'number'  => '233',
            'prefix'  => ['20', '23', '24', '26', '27', '28', '54', '55', '56', '57', '59', '24', '25', '26', '27', '28', '20', '23', '27', '24', '25', '26', '27', '28', '29', '55', '59', '20', '23', '24', '25', '26', '27', '28', '54', '55', '56', '57', '59'],
        ],
        'Gibraltar'                                    => [
            'cn_name' => '直布罗陀',
            'us_name' => 'Gibraltar',
            'number'  => '350',
            'prefix'  => [''],
        ],
        'GoSquared'                                    => [
            'cn_name' => 'GoSquared',
            'us_name' => 'GoSquared',
            'number'  => '',
            'prefix'  => [''],
        ],
        'Greece'                                       => [
            'cn_name' => '希腊',
            'us_name' => 'Greece',
            'number'  => '30',
            'prefix'  => ['69', '697', '698', '699', '7'],
        ],
        'Greenland'                                    => [
            'cn_name' => '格陵兰',
            'us_name' => 'Greenland',
            'number'  => '299',
            'prefix'  => ['4', '5', '6'],
        ],
        'Grenada'                                      => [
            'cn_name' => '格林纳达',
            'us_name' => 'Grenada',
            'number'  => '1',
            'prefix'  => ['473'],
        ],
        'Guam'                                         => [
            'cn_name' => '关岛',
            'us_name' => 'Guam',
            'number'  => '1',
            'prefix'  => ['671'],
        ],
        'Guatemala'                                    => [
            'cn_name' => '危地马拉',
            'us_name' => 'Guatemala',
            'number'  => '502',
            'prefix'  => ['3', '4', '5', '6', '7', '8'],
        ],
        'Guernsey'                                     => [
            'cn_name' => '根西岛',
            'us_name' => 'Guernsey',
            'number'  => '44',
            'prefix'  => [''],
        ],
        'Guinea-Bissau'                                => [
            'cn_name' => '几内亚比绍',
            'us_name' => 'Guinea-Bissau',
            'number'  => '245',
            'prefix'  => [''],
        ],
        'Guinea'                                       => [
            'cn_name' => '几内亚',
            'us_name' => 'Guinea',
            'number'  => '224',
            'prefix'  => [''],
        ],
        'Guyana'                                       => [
            'cn_name' => '圭亚那',
            'us_name' => 'Guyana',
            'number'  => '592',
            'prefix'  => ['6', '7'],
        ],
        'Haiti'                                        => [
            'cn_name' => '海地',
            'us_name' => 'Haiti',
            'number'  => '509',
            'prefix'  => ['3'],
        ],
        'Honduras'                                     => [
            'cn_name' => '洪都拉斯',
            'us_name' => 'Honduras',
            'number'  => '504',
            'prefix'  => ['3', '7', '8'],
        ],
        'Hong-Kong'                                    => [
            'cn_name' => '中国香港',
            'us_name' => 'Hong-Kong',
            'number'  => '852',
            'prefix'  => ['512', '513', '514', '515', '516', '517', '518', '519', '520', '521', '522', '523', '524', '525', '526', '527', '528', '529', '530', '531', '532', '533', '534', '535', '536', '537', '538', '539', '540', '541', '542', '543', '544', '545', '546', '547', '548', '549', '550', '551', '552', '553', '554', '555', '556', '557', '558', '559', '560', '561', '562', '563', '564', '565', '566', '567', '568', '569', '570', '571', '572', '573', '574', '575', '576', '577', '578', '579', '580', '581', '582', '583', '584', '585', '586', '587', '588', '589'],
        ],
        'Hungary'                                      => [
            'cn_name' => '匈牙利',
            'us_name' => 'Hungary',
            'number'  => '36',
            'prefix'  => ['20', '30', '31', '32', '33', '34', '35', '36', '37', '70', '71', '72', '73', '74', '75', '76', '77', '78', '79', '90', '91', '92', '93', '94', '95', '96', '97', '98', '99'],
        ],
        'Iceland'                                      => [
            'cn_name' => '冰岛',
            'us_name' => 'Iceland',
            'number'  => '354',
            'prefix'  => ['6', '7', '8', '9'],
        ],
        'India'                                        => [
            'cn_name' => '印度',
            'us_name' => 'India',
            'number'  => '91',
            'prefix'  => ['6', '7', '8', '9'],
        ],
        'Indonesia'                                    => [
            'cn_name' => '印度尼西亚',
            'us_name' => 'Indonesia',
            'number'  => '62',
            'prefix'  => ['8'],
        ],
        'Iran'                                         => [
            'cn_name' => '伊朗',
            'us_name' => 'Iran',
            'number'  => '98',
            'prefix'  => ['91', '92', '93', '99'],
        ],
        'Iraq'                                         => [
            'cn_name' => '伊拉克',
            'us_name' => 'Iraq',
            'number'  => '964',
            'prefix'  => ['7'],
        ],
        'Ireland'                                      => [
            'cn_name' => '爱尔兰',
            'us_name' => 'Ireland',
            'number'  => '353',
            'prefix'  => [''],
        ],
        'Isle-of-Man'                                  => [
            'cn_name' => '马恩岛',
            'us_name' => 'Isle-of-Man',
            'number'  => '44',
            'prefix'  => [''],
        ],
        'Israel'                                       => [
            'cn_name' => '以色列',
            'us_name' => 'Israel',
            'number'  => '972',
            'prefix'  => ['50', '51', '52', '53', '54', '55', '56', '57', '58'],
        ],
        'Italy'                                        => [
            'cn_name' => '意大利',
            'us_name' => 'Italy',
            'number'  => '39',
            'prefix'  => [''],
        ],
        'Jamaica'                                      => [
            'cn_name' => '牙买加',
            'us_name' => 'Jamaica',
            'number'  => '1',
            'prefix'  => ['876'],
        ],
        'Japan'                                        => [
            'cn_name' => '日本',
            'us_name' => 'Japan',
            'number'  => '81',
            'prefix'  => ['70', '80', '90'],
        ],
        'Jersey'                                       => [
            'cn_name' => '泽西岛',
            'us_name' => 'Jersey',
            'number'  => '44',
            'prefix'  => [''],
        ],
        'Jordan'                                       => [
            'cn_name' => '约旦',
            'us_name' => 'Jordan',
            'number'  => '962',
            'prefix'  => ['7'],
        ],
        'Kazakhstan'                                   => [
            'cn_name' => '哈萨克斯坦',
            'us_name' => 'Kazakhstan',
            'number'  => '7',
            'prefix'  => ['70', '71', '72', '73', '74'],
        ],
        'Kenya'                                        => [
            'cn_name' => '肯尼亚',
            'us_name' => 'Kenya',
            'number'  => '254',
            'prefix'  => ['7'],
        ],
        'Kiribati'                                     => [
            'cn_name' => '基里巴斯',
            'us_name' => 'Kiribati',
            'number'  => '686',
            'prefix'  => ['3', '4', '5', '6'],
        ],
        'Kosovo'                                       => [
            'cn_name' => '科索沃',
            'us_name' => 'Kosovo',
            'number'  => '383',
            'prefix'  => [''],
        ],
        'Kuwait'                                       => [
            'cn_name' => '科威特',
            'us_name' => 'Kuwait',
            'number'  => '965',
            'prefix'  => ['50', '51', '55', '56', '59'],
        ],
        'Kyrgyzstan'                                   => [
            'cn_name' => '吉尔吉斯斯坦',
            'us_name' => 'Kyrgyzstan',
            'number'  => '996',
            'prefix'  => ['5'],
        ],
        'Laos'                                         => [
            'cn_name' => '老挝',
            'us_name' => 'Laos',
            'number'  => '856',
            'prefix'  => ['20', '21', '30', '31', '50', '51', '52', '53', '54', '55'],
        ],
        'Latvia'                                       => [
            'cn_name' => '拉脱维亚',
            'us_name' => 'Latvia',
            'number'  => '371',
            'prefix'  => ['2', '28', '29'],
        ],
        'Lebanon'                                      => [
            'cn_name' => '黎巴嫩',
            'us_name' => 'Lebanon',
            'number'  => '961',
            'prefix'  => ['3', '7'],
        ],
        'Lesotho'                                      => [
            'cn_name' => '莱索托',
            'us_name' => 'Lesotho',
            'number'  => '266',
            'prefix'  => ['5'],
        ],
        'Liberia'                                      => [
            'cn_name' => '利比里亚',
            'us_name' => 'Liberia',
            'number'  => '231',
            'prefix'  => [''],
        ],
        'Libya'                                        => [
            'cn_name' => '利比亚',
            'us_name' => 'Libya',
            'number'  => '218',
            'prefix'  => ['9'],
        ],
        'Liechtenstein'                                => [
            'cn_name' => '列支敦士登',
            'us_name' => 'Liechtenstein',
            'number'  => '423',
            'prefix'  => ['6', '7', '9'],
        ],
        'Lithuania'                                    => [
            'cn_name' => '立陶宛',
            'us_name' => 'Lithuania',
            'number'  => '370',
            'prefix'  => ['6', '7', '8'],
        ],
        'Luxembourg'                                   => [
            'cn_name' => '卢森堡',
            'us_name' => 'Luxembourg',
            'number'  => '352',
            'prefix'  => ['6'],
        ],
        'Macau'                                        => [
            'cn_name' => '中国澳门',
            'us_name' => 'Macau',
            'number'  => '853',
            'prefix'  => ['6', '66', '68', '6', '66', '68'],
        ],
        'Macedonia'                                    => [
            'cn_name' => '马其顿',
            'us_name' => 'Macedonia',
            'number'  => '389',
            'prefix'  => ['7'],
        ],
        'Madagascar'                                   => [
            'cn_name' => '马达加斯加',
            'us_name' => 'Madagascar',
            'number'  => '261',
            'prefix'  => ['3', '4'],
        ],
        'Malawi'                                       => [
            'cn_name' => '马拉维',
            'us_name' => 'Malawi',
            'number'  => '265',
            'prefix'  => [''],
        ],
        'Malaysia'                                     => [
            'cn_name' => '马来西亚',
            'us_name' => 'Malaysia',
            'number'  => '60',
            'prefix'  => ['1'],
        ],
        'Maldives'                                     => [
            'cn_name' => '马尔代夫',
            'us_name' => 'Maldives',
            'number'  => '960',
            'prefix'  => [''],
        ],
        'Mali'                                         => [
            'cn_name' => '马里',
            'us_name' => 'Mali',
            'number'  => '223',
            'prefix'  => [''],
        ],
        'Malta'                                        => [
            'cn_name' => '马耳他',
            'us_name' => 'Malta',
            'number'  => '356',
            'prefix'  => ['79', '99'],
        ],
        'Mars'                                         => [
            'cn_name' => '火星',
            'us_name' => 'Mars',
            'number'  => '',
            'prefix'  => [''],
        ],
        'Marshall-Islands'                             => [
            'cn_name' => '马绍尔群岛',
            'us_name' => 'Marshall-Islands',
            'number'  => '692',
            'prefix'  => [''],
        ],
        'Martinique'                                   => [
            'cn_name' => '马提尼克',
            'us_name' => 'Martinique',
            'number'  => '596',
            'prefix'  => [''],
        ],
        'Mauritania'                                   => [
            'cn_name' => '毛里塔尼亚',
            'us_name' => 'Mauritania',
            'number'  => '222',
            'prefix'  => [''],
        ],
        'Mauritius'                                    => [
            'cn_name' => '毛里求斯',
            'us_name' => 'Mauritius',
            'number'  => '230',
            'prefix'  => [''],
        ],
        'Mayotte'                                      => [
            'cn_name' => '马约特岛',
            'us_name' => 'Mayotte',
            'number'  => '262',
            'prefix'  => [''],
        ],
        'Mexico'                                       => [
            'cn_name' => '墨西哥',
            'us_name' => 'Mexico',
            'number'  => '52',
            'prefix'  => ['1'],
        ],
        'Micronesia'                                   => [
            'cn_name' => '密克罗尼西亚',
            'us_name' => 'Micronesia',
            'number'  => '691',
            'prefix'  => [''],
        ],
        'Moldova'                                      => [
            'cn_name' => '摩尔多瓦',
            'us_name' => 'Moldova',
            'number'  => '373',
            'prefix'  => ['6', '7', '8'],
        ],
        'Monaco'                                       => [
            'cn_name' => '摩纳哥',
            'us_name' => 'Monaco',
            'number'  => '377',
            'prefix'  => [''],
        ],
        'Mongolia'                                     => [
            'cn_name' => '蒙古',
            'us_name' => 'Mongolia',
            'number'  => '976',
            'prefix'  => [''],
        ],
        'Montenegro'                                   => [
            'cn_name' => '黑山',
            'us_name' => 'Montenegro',
            'number'  => '382',
            'prefix'  => [''],
        ],
        'Montserrat'                                   => [
            'cn_name' => '蒙特塞拉特岛',
            'us_name' => 'Montserrat',
            'number'  => '1',
            'prefix'  => ['664'],
        ],
        'Morocco'                                      => [
            'cn_name' => '摩洛哥',
            'us_name' => 'Morocco',
            'number'  => '212',
            'prefix'  => ['6'],
        ],
        'Mozambique'                                   => [
            'cn_name' => '莫桑比克',
            'us_name' => 'Mozambique',
            'number'  => '258',
            'prefix'  => [''],
        ],
        'Myanmar'                                      => [
            'cn_name' => '缅甸',
            'us_name' => 'Myanmar',
            'number'  => '95',
            'prefix'  => ['9'],
        ],
        'NATO'                                         => [
            'cn_name' => '北约',
            'us_name' => 'NATO',
            'number'  => '',
            'prefix'  => [''],
        ],
        'Nagorno-Karabakh'                             => [
            'cn_name' => '纳戈尔诺-卡拉巴赫',
            'us_name' => 'Nagorno-Karabakh',
            'number'  => '374',
            'prefix'  => ['47'],
        ],
        'Namibia'                                      => [
            'cn_name' => '纳米比亚',
            'us_name' => 'Namibia',
            'number'  => '264',
            'prefix'  => [''],
        ],
        'Nauru'                                        => [
            'cn_name' => '瑙鲁',
            'us_name' => 'Nauru',
            'number'  => '674',
            'prefix'  => [''],
        ],
        'Nepal'                                        => [
            'cn_name' => '尼泊尔',
            'us_name' => 'Nepal',
            'number'  => '977',
            'prefix'  => ['97'],
        ],
        'Netherlands-Antilles'                         => [
            'cn_name' => '荷属安的列斯',
            'us_name' => 'Netherlands-Antilles',
            'number'  => '599',
            'prefix'  => [''],
        ],
        'Netherlands'                                  => [
            'cn_name' => '荷兰',
            'us_name' => 'Netherlands',
            'number'  => '31',
            'prefix'  => ['6'],
        ],
        'New-Caledonia'                                => [
            'cn_name' => '新喀里多尼亚',
            'us_name' => 'New-Caledonia',
            'number'  => '687',
            'prefix'  => [''],
        ],
        'New-Zealand'                                  => [
            'cn_name' => '新西兰',
            'us_name' => 'New-Zealand',
            'number'  => '64',
            'prefix'  => ['2', '3', '4', '6', '7', '9'],
        ],
        'Nicaragua'                                    => [
            'cn_name' => '尼加拉瓜',
            'us_name' => 'Nicaragua',
            'number'  => '505',
            'prefix'  => ['8', '8', '8', '8'],
        ],
        'Niger'                                        => [
            'cn_name' => '尼日尔',
            'us_name' => 'Niger',
            'number'  => '227',
            'prefix'  => [''],
        ],
        'Nigeria'                                      => [
            'cn_name' => '尼日利亚',
            'us_name' => 'Nigeria',
            'number'  => '234',
            'prefix'  => [''],
        ],
        'Niue'                                         => [
            'cn_name' => '纽埃岛',
            'us_name' => 'Niue',
            'number'  => '683',
            'prefix'  => [''],
        ],
        'Norfolk-Island'                               => [
            'cn_name' => '诺福克岛',
            'us_name' => 'Norfolk-Island',
            'number'  => '672',
            'prefix'  => [''],
        ],
        'North-Korea'                                  => [
            'cn_name' => '朝鲜',
            'us_name' => 'North-Korea',
            'number'  => '850',
            'prefix'  => [''],
        ],
        'Northern-Cyprus'                              => [
            'cn_name' => '北塞浦路斯',
            'us_name' => 'Northern-Cyprus',
            'number'  => '90',
            'prefix'  => ['5'],
        ],
        'Northern-Mariana-Islands'                     => [
            'cn_name' => '北马里亚纳群岛',
            'us_name' => 'Northern-Mariana-Islands',
            'number'  => '1',
            'prefix'  => ['670'],
        ],
        'Norway'                                       => [
            'cn_name' => '挪威',
            'us_name' => 'Norway',
            'number'  => '47',
            'prefix'  => ['4', '9'],
        ],
        'Olympics'                                     => [
            'cn_name' => '奥林匹克',
            'us_name' => 'Olympics',
            'number'  => '',
            'prefix'  => [''],
        ],
        'Oman'                                         => [
            'cn_name' => '阿曼',
            'us_name' => 'Oman',
            'number'  => '968',
            'prefix'  => ['9'],
        ],
        'Pakistan'                                     => [
            'cn_name' => '巴基斯坦',
            'us_name' => 'Pakistan',
            'number'  => '92',
            'prefix'  => ['3'],
        ],
        'Palau'                                        => [
            'cn_name' => '帕劳',
            'us_name' => 'Palau',
            'number'  => '680',
            'prefix'  => [''],
        ],
        'Palestine'                                    => [
            'cn_name' => '巴勒斯坦',
            'us_name' => 'Palestine',
            'number'  => '970',
            'prefix'  => ['5', '5', '5', '5'],
        ],
        'Panama'                                       => [
            'cn_name' => '巴拿马',
            'us_name' => 'Panama',
            'number'  => '507',
            'prefix'  => ['6'],
        ],
        'Papua-New-Guinea'                             => [
            'cn_name' => '巴布亚新几内亚',
            'us_name' => 'Papua-New-Guinea',
            'number'  => '675',
            'prefix'  => [''],
        ],
        'Paraguay'                                     => [
            'cn_name' => '巴拉圭',
            'us_name' => 'Paraguay',
            'number'  => '595',
            'prefix'  => ['9'],
        ],
        'Peru'                                         => [
            'cn_name' => '秘鲁',
            'us_name' => 'Peru',
            'number'  => '51',
            'prefix'  => ['9'],
        ],
        'Philippines'                                  => [
            'cn_name' => '菲律宾',
            'us_name' => 'Philippines',
            'number'  => '63',
            'prefix'  => ['9'],
        ],
        'Pitcairn-Islands'                             => [
            'cn_name' => '皮特凯恩岛',
            'us_name' => 'Pitcairn-Islands',
            'number'  => '870',
            'prefix'  => [''],
        ],
        'Poland'                                       => [
            'cn_name' => '波兰',
            'us_name' => 'Poland',
            'number'  => '48',
            'prefix'  => ['4', '6', '7', '8', '9'],
        ],
        'Portugal'                                     => [
            'cn_name' => '葡萄牙',
            'us_name' => 'Portugal',
            'number'  => '351',
            'prefix'  => ['9'],
        ],
        'Puerto-Rico'                                  => [
            'cn_name' => '波多黎各',
            'us_name' => 'Puerto-Rico',
            'number'  => '1',
            'prefix'  => ['787', '939'],
        ],
        'Qatar'                                        => [
            'cn_name' => '卡塔尔',
            'us_name' => 'Qatar',
            'number'  => '974',
            'prefix'  => ['3', '5', '6', '7', '8'],
        ],
        'Red-Cross'                                    => [
            'cn_name' => '红十字会',
            'us_name' => 'Red-Cross',
            'number'  => '',
            'prefix'  => [''],
        ],
        'Republic-of-the-Congo'                        => [
            'cn_name' => '刚果共和国',
            'us_name' => 'Republic-of-the-Congo',
            'number'  => '242',
            'prefix'  => [''],
        ],
        'Romania'                                      => [
            'cn_name' => '罗马尼亚',
            'us_name' => 'Romania',
            'number'  => '40',
            'prefix'  => ['7', '7'],
        ],
        'Russia'                                       => [
            'cn_name' => '俄罗斯',
            'us_name' => 'Russia',
            'number'  => '7',
            'prefix'  => ['9'],
        ],
        'Rwanda'                                       => [
            'cn_name' => '卢旺达',
            'us_name' => 'Rwanda',
            'number'  => '250',
            'prefix'  => [''],
        ],
        'Saint-Barthelemy'                             => [
            'cn_name' => '圣巴泰勒米',
            'us_name' => 'Saint-Barthelemy',
            'number'  => '590',
            'prefix'  => [''],
        ],
        'Saint-Helena'                                 => [
            'cn_name' => '圣赫勒拿',
            'us_name' => 'Saint-Helena',
            'number'  => '290',
            'prefix'  => [''],
        ],
        'Saint-Kitts-and-Nevis'                        => [
            'cn_name' => '圣基茨和尼维斯',
            'us_name' => 'Saint-Kitts-and-Nevis',
            'number'  => '1',
            'prefix'  => ['869'],
        ],
        'Saint-Lucia'                                  => [
            'cn_name' => '圣卢西亚',
            'us_name' => 'Saint-Lucia',
            'number'  => '1',
            'prefix'  => ['758'],
        ],
        'Saint-Martin'                                 => [
            'cn_name' => '圣马丁',
            'us_name' => 'Saint-Martin',
            'number'  => '590',
            'prefix'  => [''],
        ],
        'Saint-Vincent-and-the-Grenadines'             => [
            'cn_name' => '圣文森特和格林纳丁斯',
            'us_name' => 'Saint-Vincent-and-the-Grenadines',
            'number'  => '1',
            'prefix'  => ['784'],
        ],
        'Samoa'                                        => [
            'cn_name' => '萨摩亚',
            'us_name' => 'Samoa',
            'number'  => '685',
            'prefix'  => ['7'],
        ],
        'San-Marino'                                   => [
            'cn_name' => '圣马力诺',
            'us_name' => 'San-Marino',
            'number'  => '378',
            'prefix'  => [''],
        ],
        'Sao-Tome-and-Principe'                        => [
            'cn_name' => '圣多美和普林西比',
            'us_name' => 'Sao-Tome-and-Principe',
            'number'  => '239',
            'prefix'  => [''],
        ],
        'Saudi-Arabia'                                 => [
            'cn_name' => '沙特阿拉伯',
            'us_name' => 'Saudi-Arabia',
            'number'  => '966',
            'prefix'  => ['5'],
        ],
        'Scotland'                                     => [
            'cn_name' => '苏格兰',
            'us_name' => 'Scotland',
            'number'  => '44',
            'prefix'  => ['7', '7', '7', '7', '7', '7'],
        ],
        'Senegal'                                      => [
            'cn_name' => '塞内加尔',
            'us_name' => 'Senegal',
            'number'  => '221',
            'prefix'  => ['7', '8'],
        ],
        'Serbia'                                       => [
            'cn_name' => '塞尔维亚',
            'us_name' => 'Serbia',
            'number'  => '381',
            'prefix'  => ['6'],
        ],
        'Seychelles'                                   => [
            'cn_name' => '塞舌尔',
            'us_name' => 'Seychelles',
            'number'  => '248',
            'prefix'  => ['2', '4'],
        ],
        'Sierra-Leone'                                 => [
            'cn_name' => '塞拉利昂',
            'us_name' => 'Sierra-Leone',
            'number'  => '232',
            'prefix'  => ['3'],
        ],
        'Singapore'                                    => [
            'cn_name' => '新加坡',
            'us_name' => 'Singapore',
            'number'  => '65',
            'prefix'  => ['8'],
        ],
        'Slovakia'                                     => [
            'cn_name' => '斯洛伐克',
            'us_name' => 'Slovakia',
            'number'  => '421',
            'prefix'  => ['9'],
        ],
        'Slovenia'                                     => [
            'cn_name' => '斯洛文尼亚',
            'us_name' => 'Slovenia',
            'number'  => '386',
            'prefix'  => ['3'],
        ],
        'Solomon-Islands'                              => [
            'cn_name' => '所罗门群岛',
            'us_name' => 'Solomon-Islands',
            'number'  => '677',
            'prefix'  => [''],
        ],
        'Somalia'                                      => [
            'cn_name' => '索马里',
            'us_name' => 'Somalia',
            'number'  => '252',
            'prefix'  => [''],
        ],
        'Somaliland'                                   => [
            'cn_name' => '索马里兰',
            'us_name' => 'Somaliland',
            'number'  => '252',
            'prefix'  => [''],
        ],
        'South-Africa'                                 => [
            'cn_name' => '南非',
            'us_name' => 'South-Africa',
            'number'  => '27',
            'prefix'  => ['6'],
        ],
        'South-Georgia-and-the-South-Sandwich-Islands' => [
            'cn_name' => '南乔治亚岛和南桑威奇群岛',
            'us_name' => 'South-Georgia-and-the-South-Sandwich-Islands',
            'number'  => '',
            'prefix'  => [''],
        ],
        'South-Korea'                                  => [
            'cn_name' => '韩国',
            'us_name' => 'South-Korea',
            'number'  => '82',
            'prefix'  => ['1', '1', '1', '1'],
        ],
        'South-Ossetia'                                => [
            'cn_name' => '南奥塞梯',
            'us_name' => 'South-Ossetia',
            'number'  => '995',
            'prefix'  => [''],
        ],
        'South-Sudan'                                  => [
            'cn_name' => '南苏丹',
            'us_name' => 'South-Sudan',
            'number'  => '211',
            'prefix'  => [''],
        ],
        'Spain'                                        => [
            'cn_name' => '西班牙',
            'us_name' => 'Spain',
            'number'  => '34',
            'prefix'  => ['6', '6', '6', '6', '6', '7', '9'],
        ],
        'Sri-Lanka'                                    => [
            'cn_name' => '斯里兰卡',
            'us_name' => 'Sri-Lanka',
            'number'  => '94',
            'prefix'  => ['7'],
        ],
        'Sudan'                                        => [
            'cn_name' => '苏丹',
            'us_name' => 'Sudan',
            'number'  => '249',
            'prefix'  => ['9'],
        ],
        'Suriname'                                     => [
            'cn_name' => '苏里南',
            'us_name' => 'Suriname',
            'number'  => '597',
            'prefix'  => ['6'],
        ],
        'Swaziland'                                    => [
            'cn_name' => '斯威士兰',
            'us_name' => 'Swaziland',
            'number'  => '268',
            'prefix'  => [''],
        ],
        'Sweden'                                       => [
            'cn_name' => '瑞典',
            'us_name' => 'Sweden',
            'number'  => '46',
            'prefix'  => ['7'],
        ],
        'Switzerland'                                  => [
            'cn_name' => '瑞士',
            'us_name' => 'Switzerland',
            'number'  => '41',
            'prefix'  => ['7'],
        ],
        'Syria'                                        => [
            'cn_name' => '叙利亚',
            'us_name' => 'Syria',
            'number'  => '963',
            'prefix'  => ['9'],
        ],
        'Taiwan'                                       => [
            'cn_name' => '台湾',
            'us_name' => 'Taiwan',
            'number'  => '886',
            'prefix'  => ['9'],
        ],
        'Tajikistan'                                   => [
            'cn_name' => '塔吉克斯坦',
            'us_name' => 'Tajikistan',
            'number'  => '992',
            'prefix'  => ['9'],
        ],
        'Tanzania'                                     => [
            'cn_name' => '坦桑尼亚',
            'us_name' => 'Tanzania',
            'number'  => '255',
            'prefix'  => ['6'],
        ],
        'Thailand'                                     => [
            'cn_name' => '泰国',
            'us_name' => 'Thailand',
            'number'  => '66',
            'prefix'  => ['6', '8', '9'],
        ],
        'Togo'                                         => [
            'cn_name' => '多哥',
            'us_name' => 'Togo',
            'number'  => '228',
            'prefix'  => [''],
        ],
        'Tokelau'                                      => [
            'cn_name' => '托克劳',
            'us_name' => 'Tokelau',
            'number'  => '690',
            'prefix'  => [''],
        ],
        'Tonga'                                        => [
            'cn_name' => '汤加',
            'us_name' => 'Tonga',
            'number'  => '676',
            'prefix'  => ['8'],
        ],
        'Trinidad-and-Tobago'                          => [
            'cn_name' => '特立尼达和多巴哥',
            'us_name' => 'Trinidad-and-Tobago',
            'number'  => '1',
            'prefix'  => ['868'],
        ],
        'Tunisia'                                      => [
            'cn_name' => '突尼斯',
            'us_name' => 'Tunisia',
            'number'  => '216',
            'prefix'  => ['2'],
        ],
        'Turkey'                                       => [
            'cn_name' => '土耳其',
            'us_name' => 'Turkey',
            'number'  => '90',
            'prefix'  => ['5'],
        ],
        'Turkmenistan'                                 => [
            'cn_name' => '土库曼斯坦',
            'us_name' => 'Turkmenistan',
            'number'  => '993',
            'prefix'  => ['6', '7'],
        ],
        'Turks-and-Caicos-Islands'                     => [
            'cn_name' => '特克斯和凯科斯群岛',
            'us_name' => 'Turks-and-Caicos-Islands',
            'number'  => '1',
            'prefix'  => ['649'],
        ],
        'Tuvalu'                                       => [
            'cn_name' => '图瓦卢',
            'us_name' => 'Tuvalu',
            'number'  => '688',
            'prefix'  => [''],
        ],
        'US-Virgin-Islands'                            => [
            'cn_name' => '美属维尔京群岛',
            'us_name' => 'US-Virgin-Islands',
            'number'  => '1',
            'prefix'  => ['340'],
        ],
        'Uganda'                                       => [
            'cn_name' => '乌干达',
            'us_name' => 'Uganda',
            'number'  => '256',
            'prefix'  => ['7'],
        ],
        'Ukraine'                                      => [
            'cn_name' => '乌克兰',
            'us_name' => 'Ukraine',
            'number'  => '380',
            'prefix'  => ['6'],
        ],
        'United-Arab-Emirates'                         => [
            'cn_name' => '阿拉伯联合酋长国',
            'us_name' => 'United-Arab-Emirates',
            'number'  => '971',
            'prefix'  => ['5'],
        ],
        'United-Kingdom'                               => [
            'cn_name' => '英国',
            'us_name' => 'United-Kingdom',
            'number'  => '44',
            'prefix'  => ['7', '7', '7', '7', '7', '7'],
        ],
        'United-Nations'                               => [
            'cn_name' => '联合国',
            'us_name' => 'United-Nations',
            'number'  => '',
            'prefix'  => [''],
        ],
        'United-States'                                => [
            'cn_name' => '美国',
            'us_name' => 'United-States',
            'number'  => '1',
            'prefix'  => [
                '201', '202', '203', '204', '205', '206', '207', '208', '209', '210', '212', '213', '214', '215', '216', '217', '218', '219', '224', '225', '228', '229', '231', '234', '239', '240', '248', '252', '253', '254', '256', '260', '262', '267', '268', '269', '270', '276', '281', '301', '302', '303', '304', '305', '307', '308', '309', '310', '312', '313', '314', '315', '316', '317', '318', '319', '320', '321', '323', '325', '330', '331', '334', '336', '337', '339', '346', '347', '351', '352', '360', '361', '364', '380', '385', '386', '401', '402', '404', '405', '406', '407', '408', '409', '410', '412', '413', '414', '415', '417', '419', '423', '424', '425', '430', '432', '434', '435', '440', '442', '443', '445', '447', '458', '463', '464', '469', '470', '473', '475', '478', '479', '480', '484', '501', '502', '503', '504', '505', '506', '507', '508', '509', '510', '512', '513', '515', '516', '517', '518', '520', '530', '531', '534', '539', '540', '541', '551', '559', '561', '562', '563', '564', '567', '570', '571', '573', '574', '575', '580', '585', '586', '601', '602', '603', '605', '606', '607', '608', '609', '610', '612', '614', '615', '616', '617', '618', '619', '620', '623', '626', '628', '629', '630', '631', '636', '641', '646', '651', '660', '661', '662', '669', '670', '671', '678', '682', '684', '701', '702', '703', '704', '706', '707', '708', '712', '713', '714', '715', '716', '717', '718', '719', '720', '724', '727', '731', '732', '734', '737', '740', '747', '754', '757', '760', '762', '763', '765', '769', '770', '772', '773', '774', '775', '779', '781', '785', '786', '787', '801', '802', '803', '804', '805', '806', '808', '810', '812', '813', '814', '815', '816', '817', '818', '828', '830', '831', '832', '843', '845', '847', '848', '850', '854', '856', '857', '858', '859', '860', '862', '863', '864', '865', '870', '872', '878', '901', '903', '904', '906', '907', '908', '909', '910', '912', '913', '914', '915', '916', '917', '918', '919', '920', '925', '928', '929', '930', '931', '934', '936', '937', '938', '939', '940', '941', '947', '949', '951', '952', '954', '956', '959', '970', '971', '972', '973', '975', '978', '979', '980', '984', '985', '989',
            ],
        ],
        'Unknown'                                      => [
            'cn_name' => '未知',
            'us_name' => 'Unknown',
            'number'  => '',
            'prefix'  => [''],
        ],
        'Uruguay'                                      => [
            'cn_name' => '乌拉圭',
            'us_name' => 'Uruguay',
            'number'  => '598',
            'prefix'  => ['9'],
        ],
        'Uzbekistan'                                   => [
            'cn_name' => '乌兹别克斯坦',
            'us_name' => 'Uzbekistan',
            'number'  => '998',
            'prefix'  => ['9'],
        ],
        'Vanuatu'                                      => [
            'cn_name' => '瓦努阿图',
            'us_name' => 'Vanuatu',
            'number'  => '678',
            'prefix'  => [''],
        ],
        'Vatican-City'                                 => [
            'cn_name' => '梵蒂冈城',
            'us_name' => 'Vatican-City',
            'number'  => '379',
            'prefix'  => [''],
        ],
        'Venezuela'                                    => [
            'cn_name' => '委内瑞拉',
            'us_name' => 'Venezuela',
            'number'  => '58',
            'prefix'  => ['4'],
        ],
        'Vietnam'                                      => [
            'cn_name' => '越南',
            'us_name' => 'Vietnam',
            'number'  => '84',
            'prefix'  => ['9'],
        ],
        'Wales'                                        => [
            'cn_name' => '威尔士',
            'us_name' => 'Wales',
            'number'  => '44',
            'prefix'  => ['7', '7', '7', '7', '7', '7'],
        ],
        'Wallis-And-Futuna'                            => [
            'cn_name' => '瓦利斯和富图纳',
            'us_name' => 'Wallis-And-Futuna',
            'number'  => '681',
            'prefix'  => [''],
        ],
        'Western-Sahara'                               => [
            'cn_name' => '西撒哈拉',
            'us_name' => 'Western-Sahara',
            'number'  => '',
            'prefix'  => [''],
        ],
        'Yemen'                                        => [
            'cn_name' => '也门',
            'us_name' => 'Yemen',
            'number'  => '967',
            'prefix'  => ['7'],
        ],
        'Zambia'                                       => [
            'cn_name' => '赞比亚',
            'us_name' => 'Zambia',
            'number'  => '260',
            'prefix'  => ['9'],
        ],
        'Zimbabwe'                                     => [
            'cn_name' => '津巴布韦',
            'us_name' => 'Zimbabwe',
            'number'  => '263',
            'prefix'  => ['7'],
        ],
    ];

    /**
     * 初始化实例
     *
     * @return static|null
     */
    public static function instance(): ?static
    {
        if (!isset(self::$instance) || is_null(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * 获取国家代码
     *
     * @param string $country 国家名称 eg: China
     *
     * @return mixed
     */
    public function getCountryCode(string $country): mixed
    {
        if (isset($this->countries[$country])) {
            return $this->countries[$country];
        }
        return $this->countries['Unknown'];
    }

    /**
     * 获取国家手机号码前缀
     *
     * @param string $country 国家名称 eg: China
     *
     * @return mixed
     */
    public function getCountryMobilePrefix(string $country): mixed
    {
        if (isset($this->countries[$country])) {
            return $this->countries[$country]['prefix'];
        }
        return $this->countries['Unknown']['prefix'];
    }

    /**
     * 获取国家名称
     *
     * @param string $country 国家名称 eg: China
     * @param string $lang    语言 仅支持 cn,us 默认 cn
     *
     * @return mixed
     */
    public function getCountryName(string $country, string $lang = 'cn'): mixed
    {
        if (isset($this->countries[$country])) {
            return $this->countries[$country][$lang . '_name'];
        }
        return $this->countries['Unknown'][$lang . '_name'];
    }

    /**
     * 获取国家号码
     *
     * @param string $country 国家名称 eg: China
     *
     * @return mixed
     */
    public function getCountryNumber(string $country): mixed
    {
        if (isset($this->countries[$country])) {
            return $this->countries[$country]['number'];
        }
        return $this->countries['Unknown']['number'];
    }

    /**
     * 获取所有国家名称
     *
     * @param string $lang 语言 仅支持 cn,us 默认 cn
     *
     * @return array
     */
    public function getAllCountryName(string $lang = 'cn'): array
    {
        $countryName = [];
        foreach ($this->countries as $key => $value) {
            $countryName[$key] = $value[$lang . '_name'];
        }
        return $countryName;
    }

    /**
     * 获取所有国家号码(区号)
     *      有些区号是多个国家共用一个区号
     *
     * @return array
     */
    public function getAllCountryNumber(): array
    {
        $countryNumber = [];
        foreach ($this->countries as $key => $value) {
            $countryNumber[$key] = array_unique($value['number']);
        }
        return $countryNumber;
    }

    /**
     * 获取所有国家手机号码前缀
     *
     * @return array
     */
    public function getAllCountryMobilePrefix(): array
    {
        $countryMobilePrefix = [];
        foreach ($this->countries as $key => $value) {
            $countryMobilePrefix[$key] = array_unique($value['prefix']);
        }
        return $countryMobilePrefix;
    }

    /**
     * 获取所有国家信息
     *
     * @return array
     */
    public function getAllCountry(): array
    {
        return $this->countries;
    }

    /**
     * 通过国家号码(区号)获取国家名称
     *  有些区号是多个国家共用一个区号
     *
     * @param string $number
     *
     * @return array
     */
    public function getCountryNameByNumber(string $number): array
    {
        $countryName = [];
        foreach ($this->countries as $name => $value) {
            if (in_array($number, $value['number'])) {
                $countryName[] = $name;
            }
        }
        return $countryName;
    }

    /**
     * 通过国家手机号码前缀获取国家名称
     *      有些手机号码前缀是多个国家都有的
     *
     * @param string $prefix
     *
     * @return array
     */
    public function getCountryNameByMobilePrefix(string $prefix): array
    {
        $countryName = [];
        foreach ($this->countries as $name => $value) {
            if (in_array($prefix, $value['prefix'])) {
                $countryName[] = $name;
            }
        }
        return $countryName;
    }

    /**
     * 通过国家号码(区号) 获取国家手机号码前缀
     *      有些区号是多个国家共用一个区号
     *
     * @param string $number
     *
     * @return array
     */
    public function getCountryMobilePrefixByNumber(string $number): array
    {
        $countryMobilePrefix = [];
        foreach ($this->countries as $name => $value) {
            if (in_array($number, $value['number'])) {
                $countryMobilePrefix[$name] = $value['prefix'];
            }
        }
        return $countryMobilePrefix;
    }

}