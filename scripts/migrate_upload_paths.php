#!/usr/bin/env php
<?php
declare(strict_types=1);

ini_set('display_errors','0');
date_default_timezone_set('UTC');

$args = [];
foreach ($argv as $a) {
    if (strpos($a,'--')===0 && strpos($a,'=')!==false) {
        [$k,$v]=explode('=',$a,2); $args[substr($k,2)]=$v;
    } elseif ($a==='--dry-run') { $args['dry-run']='1'; }
}

$host = $args['host'] ?? '127.0.0.1';
$db   = $args['db']   ?? 'mindwareinfotech';
$user = $args['user'] ?? 'root';
$pass = $args['pass'] ?? '';
$appUrl = rtrim($args['app-url'] ?? '', '/');
$projectDir = $args['project-dir'] ?? getcwd();
$logFile = $args['log'] ?? ($projectDir . DIRECTORY_SEPARATOR . 'upload_migration_' . date('Ymd_His') . '.log');
$dry = !empty($args['dry-run']);

$logfh = fopen($logFile,'ab');
function out($s){ global $logfh; $line='['.date('c').'] '.$s.PHP_EOL; echo $line; fwrite($logfh,$line); }

try {
    $pdo = new PDO("mysql:host={$host};dbname={$db};charset=utf8mb4",$user,$pass,[
        PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC
    ]);

    out("START host={$host} db={$db} dry_run=".($dry?'yes':'no')." app_url={$appUrl}");

    $sqlCols = "SELECT TABLE_NAME, COLUMN_NAME, DATA_TYPE
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = :db
                  AND DATA_TYPE IN ('varchar','text','mediumtext','longtext','tinytext')";
    $stmt = $pdo->prepare($sqlCols);
    $stmt->execute(['db'=>$db]);
    $cols = $stmt->fetchAll();
    if (!$cols) { out("No candidate columns found."); exit(0); }

    $pdo->beginTransaction();
    $totalTables=0; $totalRows=0; $totalUpdates=0;

    foreach ($cols as $c) {
        $table = $c['TABLE_NAME']; $col = $c['COLUMN_NAME'];
        $q1 = "SELECT COUNT(*) AS n FROM `{$table}` WHERE `{$col}` LIKE '%/storage/uploads/%'";
        $n1 = (int)$pdo->query($q1)->fetchColumn();
        $n2 = 0;
        if ($appUrl !== '') {
            $like = $pdo->quote($appUrl.'/%/storage/uploads/%');
            $q2 = "SELECT COUNT(*) AS n FROM `{$table}` WHERE `{$col}` LIKE {$like}";
            $n2 = (int)$pdo->query($q2)->fetchColumn();
        }
        $hits = $n1 + $n2;
        if ($hits === 0) continue;

        $totalTables++;
        $totalRows += $hits;
        out("TABLE {$table}.{$col} matches={$hits}");

        if ($dry) continue;

        if ($appUrl !== '') {
            $upd1 = $pdo->prepare("UPDATE `{$table}` SET `{$col}` = REPLACE(`{$col}`, :old, '/uploads/') WHERE `{$col}` LIKE :like");
            $upd1->execute(['old'=>$appUrl.'/storage/uploads/','like'=>'%'.$appUrl.'/storage/uploads/%']);
            $aff1 = $upd1->rowCount(); $totalUpdates += $aff1;
            out("  updated full URLs: {$aff1}");
        }

        $upd2 = $pdo->prepare("UPDATE `{$table}` SET `{$col}` = REPLACE(`{$col}`, '/storage/uploads/', '/uploads/') WHERE `{$col}` LIKE '%/storage/uploads/%'");
        $upd2->execute();
        $aff2 = $upd2->rowCount(); $totalUpdates += $aff2;
        out("  updated relative URLs: {$aff2}");
    }

    if ($dry) {
        $pdo->rollBack();
        out("DRY-RUN complete: tables={$totalTables} rows_with_matches={$totalRows} rows_to_update_estimated={$totalRows}");
    } else {
        $pdo->commit();
        out("COMMIT complete: tables={$totalTables} rows_processed={$totalRows} rows_updated={$totalUpdates}");
    }

    if (!empty($projectDir) && is_dir($projectDir)) {
        $cnt = 0;
        $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($projectDir, FilesystemIterator::SKIP_DOTS));
        foreach ($rii as $file) {
            $ext = strtolower(pathinfo((string)$file, PATHINFO_EXTENSION));
            if (!in_array($ext,['php','html','js','css'])) continue;
            $s = @file_get_contents((string)$file);
            if ($s !== false && (stripos($s,'/storage/uploads/')!==false || stripos($s,'storage\\uploads\\')!==false)) {
                $cnt++;
                out("CODE_REFERENCE: " . str_replace($projectDir.'\\','', (string)$file));
            }
        }
        out("CODE scan complete; files_with_references={$cnt}");
    }

    out("END");
} catch (Throwable $e) {
    if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
    out("ERROR: ".$e->getMessage());
    exit(1);
}