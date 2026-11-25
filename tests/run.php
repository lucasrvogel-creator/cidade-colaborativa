<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';
use App\Services\{OccurrenceState as State, Validation as V, HttpError, ImageUpload};
$passed=0; $failed=0;
function check(string $name,callable $test): void {
    global $passed,$failed;
    try { $test(); $passed++; echo "PASS $name\n"; } catch(Throwable $e) { $failed++; echo "FAIL $name: {$e->getMessage()}\n"; }
}
function expect(bool $value,string $message='Condição não atendida'): void { if(!$value) throw new RuntimeException($message); }
function rejects(callable $test,int $status=422): void {
    try { $test(); } catch(HttpError $e) { expect($e->status===$status);return; } throw new RuntimeException('Operação deveria ser rejeitada.');
}
check('Todas as transições previstas são aceitas',function(){foreach(State::TRANSITIONS as $from=>$targets) foreach($targets as $to) State::validate($from,$to,'Justificativa de teste.');});
check('Transições fora da máquina de estados são rejeitadas',function(){foreach(State::TRANSITIONS as $from=>$targets)foreach(array_keys(State::TRANSITIONS) as $to)if(!in_array($to,$targets,true))rejects(fn()=>State::validate($from,$to,'Justificativa de teste.'));});
check('Rejeição exige justificativa',fn()=>rejects(fn()=>State::validate('PENDENTE','REJEITADA','')));
check('Reabertura exige justificativa',fn()=>rejects(fn()=>State::validate('RESOLVIDA','EM_ACOMPANHAMENTO','')));
check('Coordenadas válidas',fn()=>expect(V::coordinates('-27.214','-49.643')===[-27.214,-49.643]));
check('Latitude fora do intervalo',fn()=>rejects(fn()=>V::coordinates(91,0)));
check('Longitude fora do intervalo',fn()=>rejects(fn()=>V::coordinates(0,-181)));
check('Localização vazia',fn()=>rejects(fn()=>V::coordinates('',null)));
check('Coordenadas infinitas',fn()=>rejects(fn()=>V::coordinates(INF,0)));
check('Senhas divergentes',fn()=>rejects(fn()=>V::password(['senha'=>'abc12345678','confirmacao'=>'abc12345679'])));
check('Senha curta',fn()=>rejects(fn()=>V::password(['senha'=>'123','confirmacao'=>'123'])));
check('Escape HTML bloqueia markup',fn()=>expect(e('<script>alert("x")</script>')==='&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;'));
check('Fonte não permite javascript',fn()=>rejects(fn()=>V::sourceUrl('javascript:alert(1)')));
check('Imagem acima de 5 MB',fn()=>rejects(fn()=>ImageUpload::inspect(__FILE__,5*1024*1024+1)));
check('Arquivo PHP não é imagem',fn()=>rejects(fn()=>ImageUpload::inspect(__FILE__,filesize(__FILE__))));
check('Identificador rejeita SQL',fn()=>rejects(fn()=>V::id('1 OR 1=1')));
echo "\n$passed aprovados; $failed falhas.\n"; exit($failed?1:0);
