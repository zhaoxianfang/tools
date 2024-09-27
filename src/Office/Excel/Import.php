<?php

namespace zxf\Office\Excel;

use Closure;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Excel as ExcelExtension;
use Maatwebsite\Excel\Facades\Excel;
use zxf\Office\Excel\Imports\ExcelToArray;
use zxf\Office\Excel\Imports\ExcelToCollection;
use PhpOffice\PhpSpreadsheet\IOFactory;
use zxf\Tools\Collection;

/**
 * Excel 导入
 */
class Import
{
    // 文件对象
    protected mixed $file;

    // 是否使用Excel列名命名
    protected bool $useExcelColumnName = false;

    // 列名映射 eg: ['A' => 'name', 'B' => 'email'] 表示A列映射为name，B列映射为email
    protected array        $columnMapping = [];
    protected Closure|null $validateRowFunc;

    /**
     * 导入文件后缀
     *
     * @var string xlsx、xls、csv等
     */
    protected string $ext    = 'xlsx';
    protected array  $extMap = [
        'xlsx' => ExcelExtension::XLSX,
        'xls'  => ExcelExtension::XLS,
        'csv'  => ExcelExtension::CSV,
    ];

    public function __construct(mixed $filePath = null)
    {
        if (!class_exists(Excel::class)) {
            throw new Exception('依赖于excel，请先安装「composer require maatwebsite/excel」后再使用');
        }
        $filePath && $this->setFile($filePath);
    }

    public static function init(mixed $filePath = null): static
    {
        return new static($filePath);
    }

    /**
     * 设置文件对象
     *
     * @param mixed $file 文件路径或文件对象 eg: /path/to/file.xlsx、 $request->file('file')
     *
     * @return $this
     * @throws Exception
     */
    public function setFile(mixed $file)
    {
        if (empty($file)) {
            throw new Exception('传入的文件不能为空');
        }
        if (is_a($file, UploadedFile::class)) {
            $this->file = $file;
        } elseif (is_file($file)) {
            $this->file = new UploadedFile($file, basename($file));
        } elseif (is_string($file)) {
            $this->file = request()->file($file);
        } else {
            throw new Exception('传入的文件不是文件路径或上传文件对象');
        }
        if (!$this->file || !$this->file->isValid()) {
            throw new Exception('文件无效或无法读取');
        }
        return $this;
    }

    public function getFilePath()
    {
        return $this->file->getPathname();
    }

    /**
     * 是否使用Excel列名
     *
     * @param $status
     *
     * @return $this
     */
    public function useExcelColumnName(bool $status = true): static
    {
        $this->useExcelColumnName = $status;
        return $this;
    }


    /**
     * 设置列名映射（会自动设置为使用使用Excel列名），没有映射的列会被忽略
     *      eg: ['A' => 'name', 'B' => 'email'] 表示A列映射为name，B列映射为email
     */
    public function setColumnMapping(array $columnMapping): static
    {
        $this->useExcelColumnName(true);
        $this->columnMapping = $columnMapping;
        return $this;
    }

    /**
     * 校验文件
     *
     * @param callable $callback          回调函数
     *                                    eg:->validateFile(function () {
     *                                    // 不论传入的文件表单名称是什么，都会被重命名为file
     *                                    $rule = [
     *                                    'file' => 'required|file|max:1024|mimes:xlsx,xls,csv', // 文件最大1MB，仅限
     *                                    xlsx,xls,csv 格式
     *                                    ];
     *                                    $messages = [
     *                                    'file.required' => '文件不能为空',
     *                                    'file.max'      => '文件最大1MB',
     *                                    'file.mimes'    => '不支持的文件格式',
     *                                    ];
     *                                    return [$rule,$messages]
     *                                    })
     *
     * @return $this
     * @throws ValidationException|Exception
     */
    public function validateFile(callable $callback)
    {
        // 定义验证规则
        list($rules, $messages) = $callback();
        $validator = Validator::make(['file' => $this->file], $rules, $messages);

        if ($validator->fails()) {
            // 如果验证失败，则抛出异常
            throw new Exception($validator->errors()->first());
        }

        // 如果验证成功，则可以继续处理文件
        return $this;
    }

    /**
     * 自定义校验行数据
     *
     * @param Closure $func 自定义验证回调函数
     *                      $row: 当前验证行的数据
     *                      $rowIndex: 当前验证行的行号,从0开始
     *                      $sheetIndex: 当前验证的sheet表id，从0开始
     *                      $sheetName: 当前验证的sheet表名称
     *                      eg: function ($row, $rowIndex, $sheetIndex, $sheetName) {
     *                          // 直接返回 错误消息字符串
     *                          return "「{$sheetName}」第{$rowIndex}行的邮箱号格式错误";
     *
     *                          // 返回验证器规则和错误消息数组
     *                          $rules = [
     *                              // 这里键值可能是数字(第n列)、英文字母（列名 eg:A,B,C...）或自定映射名称(通过 setColumnMapping 方法定义)
     *                              'name' => 'required|min:2|max:25', // 文件最大2MB，仅限 xlsx, xls,csv 格式
     *                          ];
     *                          $messages = [
     *                              'name.required' => '姓名不能为空',
     *                              'name.min'      => '姓名至少2个字',
     *                              'name.min'      => '姓名不能超过25个字',
     *                          ];
     *                          return [$rules,$messages];
     *                      }
     *
     * @return $this
     */
    public function validateRow(Closure $func)
    {
        $this->validateRowFunc = $func;
        return $this;
    }

    /**
     * 获取Excel列名，例如：A,B,C,D,E,F,G,H,I,J,K,L,M...
     *
     * @param $index
     *
     * @return string
     */
    private function getExcelColumnName($index): string
    {
        $letters = '';
        while ($index >= 0) {
            $letters = chr($index % 26 + 65) . $letters;
            $index   = intdiv($index, 26) - 1;
        }
        return $letters;
    }

    /**
     * 获取Excel工作表名称（sheet name）
     *
     * @return array
     */
    public function getSheetNames(): array
    {
        // 使用 PhpSpreadsheet 的 IOFactory 加载文件
        $spreadsheet = IOFactory::load($this->getFilePath());
        $names       = [];
        // 遍历所有工作表
        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            // 输出工作表名称
            $names[] = $sheetName;
        }
        return $names;
    }

    /**
     * 导入Excel文件，返回数组
     *
     * @return array
     * @throws Exception
     */
    public function toArray()
    {
        // 获取Excel工作表名称（sheet name）
        $sheetNames = $this->getSheetNames();

        // 支持读多表
        $import = new ExcelToArray();

        // 验证数据

        $results    = Excel::toArray($import, $this->file);
        $mergedData = [];
        foreach ($results as $sheetIndex => $sheetData) {
            $sheetTableData = []; // 每个工作表的数据
            // sheet 表名称
            $sheetName  = isset($sheetNames[$sheetIndex]) ? $sheetNames[$sheetIndex] : $sheetIndex;
            foreach ($sheetData as $rowIndex => $row) {
                $items = [];
                if ($this->useExcelColumnName) {
                    foreach ($row as $key => $item) {
                        $columnName = $this->getExcelColumnName($key); // 列名
                        if (!empty($this->columnMapping)) {
                            if (isset($this->columnMapping[$columnName])) {
                                $columnName         = $this->columnMapping[$columnName];
                                $items[$columnName] = $item;
                            }
                        } else {
                            $items[$columnName] = $item;
                        }
                    }
                } else {
                    $items = $row;
                }
                // 添加自定义验证规格
                if ($this->validateRowFunc && is_callable($customCallback = $this->validateRowFunc)) {
                    $callRes = $customCallback($items, $rowIndex, $sheetIndex, $sheetName);
                    // 返回数组 eg:[[...rules...],[...$messages...]]
                    if (is_array($callRes)) {
                        if(count($callRes) != 2 || !is_array($callRes[0]) || !is_array($callRes[1])){
                            throw new Exception('自定义验证规则错误');
                        }
                        list($rules, $messages) = $callRes;
                        $validator = Validator::make($items, $rules, $messages);
                        // 验证失败则抛出异常
                        if ($validator->fails()) {
                            // 如果验证失败，则抛出异常
                            throw new Exception($validator->errors()->first());
                        }
                    }
                    // 返回错误消息，则抛出异常
                    if (is_string($callRes)) {
                        throw new Exception($callRes);
                    }

                }
                $sheetTableData[] = $items;
            }
            $mergedData[] = [
                'sheet_index' => $sheetIndex,
                'sheet_name'  => $sheetName,
                'sheet_data'  => $sheetTableData,
            ];
        }
        return $mergedData;
    }


    /**
     * 导入数据转换为集合
     *
     * @return Collection
     * @throws Exception
     */
    public function toCollect()
    {
        return new Collection($this->toArray());
    }
}
