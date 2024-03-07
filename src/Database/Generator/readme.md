```

if (!function_exists('dd')) {
    #[NoReturn]
    function dd(mixed ...$vars): void
    {
        var_dump(...$vars);
        exit(1);
    }
}
$config = [
    'host'     => '127.0.0.1',
    'dbname'   => 'test',
    'username' => 'root',
    'password' => '',
];

$dns       = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
$pdoConfig = [$dns, $config['username'], $config['password']];

try {
    $pdoIc = new \ReflectionClass('pdo');
    $conn  = $pdoIc->newInstanceArgs($pdoConfig);
    $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
} catch (\PDOException $e) {
    throw new Exception("Database 连接失败：" . $e->getMessage());
}


$queryBuilder = new MySQLQueryBuilder();

// SELECT 查询
$subQuery = (new MySQLQueryBuilder())->table('sub_table_name', 'sub_t')
    ->select(['sub_t.column1', 'sub_t.column2'])
    ->where('sub_t.column3', '>', 10, 'AND');


$query = $queryBuilder
    ->table('users', 'u')
    ->select(['u.id', 'u.uuid', 'u.nickname'])
    ->where('u.id', '>', 1, 'AND')
    ->orWhere('u.nickname', 'LIKE', '赵%')
    ->where(function ($q) {
        $q->where('u.cover', 'LIKE', '%sinaimg.cn%');
    })
    ->whereExists(function ($q) {
        $q->where('exist', 'LIKE', '%exist%');
    })
    ->whereIn('u.id', [1, 2, 3])
    ->join('table2', 't.id = table2.id', 'LEFT', 't2')
    ->joinSub($subQuery, 'sub_as_name', function ($join) {
        $join->on('sub_as_name.id = u.id')
            ->where('sub_as_name.test_field', '=', 1);
    })
    ->groupBy('u.status')
    ->having('SUM(u.status)', '>', 1)
    ->orderBy(['u.status ASC'])
    ->limit(0, 10)// ->toSql()
;


print_r($query->buildQuery());
print_r($query->getBindings());


// INSERT 查询
$query = $queryBuilder
    ->table('users')
    ->create([
        'id'       => 1,
        'uuid'     => 'uuid_1',
        'nickname' => 'nickname_1',
    ]);
$query = $queryBuilder
    ->table('users')
    ->create([
        [
            'id'       => 1,
            'uuid'     => 'uuid_1',
            'nickname' => 'nickname_1',
        ],
        [
            'id'       => 2,
            'uuid'     => 'uuid_2',
            'nickname' => 'nickname_2',
        ],
    ]);
print_r($query->buildQuery());
print_r($query->getBindings());

// UPDATE 查询
$query = $queryBuilder
    ->table('users')
    ->where('id', '>', 1)
    ->update([
        'uuid'     => 'uuid_1',
        'nickname' => 'nickname_1',
    ]);

print_r($query->buildQuery());
print_r($query->getBindings());

// UPSERT 查询
$query = $queryBuilder
    ->table('users')
    ->upsert([
        [
            'id'       => 1,
            'uuid'     => 'uuid_1',
            'nickname' => 'nickname_1',
        ],
        [
            'id'       => 2,
            'uuid'     => 'uuid_2',
            'nickname' => 'nickname_2',
        ],
    ], ['id']);

print_r($query->buildQuery());
print_r($query->getBindings());


// 是否存在 查询
$query = $queryBuilder
    ->table('users')
    ->where('id', '>', 1)
    // ->exists();
    ->doesntExist();

print_r($query->buildQuery());
print_r($query->getBindings());

// DELETE 查询
$query = $queryBuilder
    ->table('users')
    ->where('id', '>', 1)
    ->delete();

print_r($query->buildQuery());
print_r($query->getBindings());

die;
try {
    $stmt = $conn->prepare($query->buildQuery());
    $stmt->execute($queryBuilder->getBindings());
    $query->reset();
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    $query->reset();
    throw new Exception("数据库错误：" . $e->getMessage());
}

```