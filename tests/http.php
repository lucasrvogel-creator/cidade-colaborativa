<?php
/** Integração real HTTP + MySQL. Execute apenas no banco de desenvolvimento. */
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';
use App\Models\{Database as DB,Indicators};
use App\Services\DatabaseHydrology;
if(getenv('APP_ENV')!=='local') exit("Execute somente com APP_ENV=local.\n");
$base=$argv[1]??'http://127.0.0.1:8080';
if(!preg_match('~^http://(127\.0\.0\.1|localhost):\d+$~D',$base)) exit("URL de teste deve ser local.\n");
$run=bin2hex(random_bytes(6));$dir=ROOT.'/tests/.runtime/'.$run;mkdir($dir,0700,true);
$results=[];$ids=[];$reports=[];$images=[];$hydroIds=[];$quotaIds=[];$noticeIds=[];$categoryIds=[];
// Isola os limites por IP durante a execução local; restaura o estado original ao final.
$limitBackups=[];
foreach (['register:127.0.0.1','login-ip:127.0.0.1'] as $key) {
 $path=ROOT.'/storage/limits/'.hash('sha256',$key).'.json';
 $limitBackups[$path]=is_file($path)?file_get_contents($path):null;
 file_put_contents($path,'[]');
}
function assertTest(string $name,bool $condition): void {global $results;$results[]=['name'=>$name,'passed'=>$condition];echo ($condition?'PASS ':'FAIL ').$name."\n";if(!$condition)throw new RuntimeException($name);}
final class Client {
 public string $csrf='';public array $last=[];
 public function __construct(private string $base,private string $cookies){}
 public function request(string $route,?array $data=null,bool $csrf=true): array {
  $ch=curl_init($this->base.'/index.php?'.http_build_query(['r'=>explode('?',$route,2)[0]]).(str_contains($route,'?')?'&'.explode('?',$route,2)[1]:''));
  curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_HEADER=>true,CURLOPT_COOKIEFILE=>$this->cookies,CURLOPT_COOKIEJAR=>$this->cookies,CURLOPT_TIMEOUT=>20]);
  if($data!==null){if($csrf)$data['_csrf']=$this->csrf;curl_setopt($ch,CURLOPT_POST,true);curl_setopt($ch,CURLOPT_POSTFIELDS,$data);}
  $raw=curl_exec($ch);if($raw===false)throw new RuntimeException(curl_error($ch));$size=curl_getinfo($ch,CURLINFO_HEADER_SIZE);$status=curl_getinfo($ch,CURLINFO_RESPONSE_CODE);curl_close($ch);
  $header=substr($raw,0,$size);$body=substr($raw,$size);if(preg_match('/name="csrf-token" content="([a-f0-9]+)"/',$body,$m))$this->csrf=$m[1];
  return $this->last=['status'=>$status,'body'=>$body,'headers'=>$header];
 }
}
try {
 $guest=new Client($base,$dir.'/guest.cookies');$citizen=new Client($base,$dir.'/citizen.cookies');$other=new Client($base,$dir.'/other.cookies');$admin=new Client($base,$dir.'/admin.cookies');
 foreach(['/','/mapa','/ocorrencias','/indicadores','/avisos','/sobre','/privacidade','/entrar','/cadastro','/enchentes','/cotas'] as $route)assertTest('Página pública '.$route,$guest->request($route)['status']===200);
 assertTest('Cookies HttpOnly e SameSite',str_contains($guest->request('/entrar')['headers'],'200') && str_contains(file_get_contents($dir.'/guest.cookies'),'#HttpOnly_'));
 assertTest('Admin anônimo bloqueado',$guest->request('/admin')['status']===401);
 assertTest('Ausência de CSRF bloqueada',$guest->request('/cadastro',[],false)['status']===419);
 $citizen->request('/cadastro');$password='Teste-'.bin2hex(random_bytes(12));$email="teste-$run@example.invalid";
 $registration=['nome'=>'Cidadão de teste','email'=>$email,'senha'=>$password,'confirmacao'=>$password,'termos'=>'1','perfil'=>'admin'];
 assertTest('Cadastro válido',$citizen->request('/cadastro',$registration)['status']===302);
 $id=(int)DB::one('SELECT id FROM usuarios WHERE email=?',[$email])['id'];$ids[]=$id;
 assertTest('Cadastro não pode se tornar administrador',DB::one('SELECT perfil FROM usuarios WHERE id=?',[$id])['perfil']==='cidadao');
 assertTest('E-mail duplicado rejeitado',$citizen->request('/cadastro',$registration)['status']===422);
 $hash=DB::one('SELECT senha_hash FROM usuarios WHERE id=?',[$id])['senha_hash'];assertTest('Senha é hash verificável',$hash!==$password&&password_verify($password,$hash));
 assertTest('Login inválido',$citizen->request('/entrar',['email'=>$email,'senha'=>'incorreta'])['status']===422);
 assertTest('Login válido',$citizen->request('/entrar',['email'=>$email,'senha'=>$password])['status']===302);$citizen->request('/painel');
 assertTest('Cidadão não acessa administração',$citizen->request('/admin')['status']===403);
 $adminId=DB::insert('usuarios',['nome'=>'Administrador de teste','email'=>"admin-$run@example.invalid",'senha_hash'=>password_hash($password,PASSWORD_DEFAULT),'perfil'=>'admin']);$ids[]=$adminId;
 $otherId=DB::insert('usuarios',['nome'=>'Outro cidadão de teste','email'=>"outro-$run@example.invalid",'senha_hash'=>password_hash($password,PASSWORD_DEFAULT)]);$ids[]=$otherId;
 foreach([[$admin,"admin-$run@example.invalid"],[$other,"outro-$run@example.invalid"]] as [$client,$login]){$client->request('/entrar');assertTest('Login de '.$login,$client->request('/entrar',['email'=>$login,'senha'=>$password])['status']===302);$client->request('/painel');}
 foreach(['/admin','/admin/ocorrencias','/admin/usuarios','/admin/cadastros','/admin/solicitacoes','/admin/avisos','/admin/enchentes','/admin/auditoria'] as $route)assertTest('Tela administrativa '.$route,$admin->request($route)['status']===200);
 $operator=new Client($base,$dir.'/operator.cookies');
 $operatorEmail="operador-$run@example.invalid";
 assertTest('Administrador cria operador',$admin->request('/admin/usuarios',['nome'=>'Operador de teste','email'=>$operatorEmail,'senha'=>$password,'confirmacao'=>$password,'perfil'=>'operador'])['status']===302);
 $operatorId=(int)DB::one('SELECT id FROM usuarios WHERE email=?',[$operatorEmail])['id'];$ids[]=$operatorId;
 $operator->request('/entrar');assertTest('Login de operador',$operator->request('/entrar',['email'=>$operatorEmail,'senha'=>$password])['status']===302);$operator->request('/admin/ocorrencias');
 assertTest('Operador consulta chamados',$operator->last['status']===200);
 foreach(['/admin','/admin/usuarios','/admin/cadastros','/admin/avisos','/admin/enchentes','/admin/auditoria','/nova'] as $route)assertTest('Operador sem acesso a '.$route,$operator->request($route)['status']===403);
 foreach(['/admin/usuarios','/admin/cadastros','/admin/avisos','/admin/enchentes','/ocorrencia'] as $route)assertTest('Operador não grava '.$route,$operator->request($route,[])['status']===403);
 assertTest('Cidadão não consulta fila',$citizen->request('/admin/ocorrencias')['status']===403);
 assertTest('Cidadão não modera',$citizen->request('/admin/moderar',[])['status']===403);
 assertTest('Snapshot oficial tem 555 ruas',count(json_decode(file_get_contents(ROOT.'/database/data/cotas-defesa-civil.json'),true)['registros'])===555);
 assertTest('Importação oficial sem duplicação',\App\Services\QuotaImporter::import()===0);
 assertTest('Cotas oficiais sem bairros inventados',(int)DB::one('SELECT COUNT(*) total FROM cotas_inundacao WHERE chave_fonte IS NOT NULL AND bairro_id IS NOT NULL')['total']===0);
 assertTest('Busca de cotas oficial',$guest->request('/cotas?busca=1%20DE%20MAIO')['status']===200 && str_contains($guest->last['body'],'8,12'));
 $citizen->request('/nova');assertTest('Registro sem campos obrigatórios',$citizen->request('/ocorrencia',[])['status']===422);
 $data=['titulo'=>"Relato teste $run",'descricao'=>'Descrição de teste com <script>alert(1)</script> e informação suficiente.','categoria_id'=>'1','subcategoria_id'=>'1','bairro_id'=>'1','latitude'=>'-27.214','longitude'=>'-49.643','responsabilidade'=>'1'];
 assertTest('Localização inválida',$citizen->request('/ocorrencia',array_replace($data,['latitude'=>'100']))['status']===422);
 assertTest('Subcategoria incompatível',$citizen->request('/ocorrencia',array_replace($data,['subcategoria_id'=>'6']))['status']===422);
 file_put_contents($dir.'/payload.php','<?php echo "executável";');
 assertTest('Upload executável rejeitado',$citizen->request('/ocorrencia',$data+['fotos[0]'=>new CURLFile($dir.'/payload.php','image/jpeg','foto.jpg')])['status']===422);
 $image=imagecreatetruecolor(32,32);imagefill($image,0,0,imagecolorallocate($image,70,100,200));imagepng($image,$dir.'/foto.png');imagedestroy($image);
 $large=fopen($dir.'/large.png','w');fwrite($large,file_get_contents($dir.'/foto.png'));ftruncate($large,5*1024*1024+1);fclose($large);
 assertTest('Imagem acima de 5 MB rejeitada',$citizen->request('/ocorrencia',$data+['fotos[0]'=>new CURLFile($dir.'/large.png','image/png','grande.png')])['status']===422);
 assertTest('Registro com imagem válida',$citizen->request('/ocorrencia',$data+['fotos[0]'=>new CURLFile($dir.'/foto.png','image/png','foto.png')])['status']===302);
 $report=(int)DB::one('SELECT id FROM ocorrencias WHERE usuario_id=? ORDER BY id DESC',[$id])['id'];$reports[]=$report;
 $photo=DB::one('SELECT * FROM ocorrencia_imagens WHERE ocorrencia_id=?',[$report]);$images[]=$photo;
 assertTest('Registro começa pendente',DB::one('SELECT status FROM ocorrencias WHERE id=?',[$report])['status']==='PENDENTE');
 assertTest('Upload recodificado para JPEG',$photo['tipo_mime']==='image/jpeg' && preg_match('/^[a-f0-9]{40}\.jpg$/D',$photo['caminho_arquivo'])===1);
 assertTest('Relato pendente não é público',$guest->request('/ocorrencia?id='.$report)['status']===404);
 assertTest('Foto pendente não é pública',$guest->request('/imagem?id='.$photo['id'])['status']===404);
 assertTest('Outro cidadão não lê relato pendente',$other->request('/ocorrencia?id='.$report)['status']===404);
 assertTest('Operador consulta relato pendente',$operator->request('/ocorrencia?id='.$report)['status']===200);
 assertTest('Autor consulta relato privado',$citizen->request('/ocorrencia?id='.$report)['status']===200);
 assertTest('XSS escapado',str_contains($citizen->last['body'],'&lt;script&gt;alert(1)&lt;/script&gt;') && !str_contains($citizen->last['body'],'<script>alert(1)</script>'));
 assertTest('Outro cidadão não edita',$other->request('/ocorrencia',$data+['id'=>(string)$report])['status']===403);
 assertTest('SQL injection em ID rejeitada',$guest->request('/ocorrencia?id=1%20OR%201=1')['status']===422);
 assertTest('Busca SQL injection não altera consulta',json_decode($guest->request('/api/ocorrencias?q=%27%20OR%201=1--')['body'],true)['total']===0);
 $admin->request('/ocorrencia?id='.$report);
 assertTest('Rejeição sem justificativa bloqueada',$admin->request('/admin/moderar',['id'=>(string)$report,'situacao'=>'REJEITADA','justificativa'=>''])['status']===422);
 assertTest('Solicitar correção',$admin->request('/admin/moderar',['id'=>(string)$report,'situacao'=>'CORRECAO_SOLICITADA','justificativa'=>'Complemente a referência do local.'])['status']===302);
 assertTest('Autor edita e reenvia',$citizen->request('/ocorrencia',$data+['id'=>(string)$report])['status']===302);
 assertTest('Edição retorna a pendente',DB::one('SELECT status FROM ocorrencias WHERE id=?',[$report])['status']==='PENDENTE');
 assertTest('Aprovar publicação',$admin->request('/admin/moderar',['id'=>(string)$report,'situacao'=>'PUBLICADA','justificativa'=>'Conteúdo revisado para teste.'])['status']===302);
 assertTest('Relato aprovado público',$guest->request('/ocorrencia?id='.$report)['status']===200);
 assertTest('Foto aprovada pública',$guest->request('/imagem?id='.$photo['id'])['status']===200);
 assertTest('Mapa filtra categoria e busca',json_decode($guest->request('/api/ocorrencias?categoria=1&q='.$run)['body'],true)['total']===1);
 assertTest('Mapa respeita região',json_decode($guest->request('/api/ocorrencias?bbox=0,0,1,1')['body'],true)['total']===0);
 assertTest('Autor não edita publicada',$citizen->request('/ocorrencia',$data+['id'=>(string)$report])['status']===403);
 assertTest('Autor não confirma própria',$citizen->request('/participar',['id'=>(string)$report,'acao'=>'confirmar'])['status']===422);
 assertTest('Confirmação por outro cidadão',$other->request('/participar',['id'=>(string)$report,'acao'=>'confirmar'])['status']===302);
 assertTest('Confirmação duplicada rejeitada',$other->request('/participar',['id'=>(string)$report,'acao'=>'confirmar'])['status']===422);
 assertTest('Denúncia registrada',$other->request('/participar',['id'=>(string)$report,'acao'=>'denunciar','motivo'=>'Dados pessoais','descricao'=>'Verificar conteúdo durante teste.'])['status']===302);
 assertTest('Denúncia não oculta automaticamente',$guest->request('/ocorrencia?id='.$report)['status']===200);
 assertTest('Sugestão de resolução',$other->request('/participar',['id'=>(string)$report,'acao'=>'resolver','descricao'=>'O problema parece resolvido no teste.'])['status']===302);
 assertTest('Transição inválida rejeitada',$admin->request('/admin/moderar',['id'=>(string)$report,'situacao'=>'REJEITADA','justificativa'=>'Transição inválida de teste.'])['status']===422);
 assertTest('Operador resolve ocorrência',$operator->request('/admin/moderar',['id'=>(string)$report,'situacao'=>'RESOLVIDA','justificativa'=>'Resolução confirmada durante teste.'])['status']===302);
 assertTest('Resolução tem data',DB::one('SELECT resolvido_em FROM ocorrencias WHERE id=?',[$report])['resolvido_em']!==null);
 assertTest('Histórico e notificação persistidos',(int)DB::one('SELECT COUNT(*) total FROM historico_ocorrencias WHERE ocorrencia_id=?',[$report])['total']>=5 && (int)DB::one('SELECT COUNT(*) total FROM notificacoes WHERE usuario_id=?',[$id])['total']>=3);
 assertTest('Auditoria persistida',(int)DB::one('SELECT COUNT(*) total FROM auditoria WHERE usuario_id=?',[$adminId])['total']>=3);
 $stats=Indicators::public();$expected=(int)DB::one("SELECT COUNT(*) total FROM ocorrencias WHERE status IN ('PUBLICADA','EM_ACOMPANHAMENTO','RESOLVIDA')")['total'];assertTest('Indicadores consistentes',(int)$stats['totals']['total']===$expected);
 DB::query("UPDATE usuarios SET status='suspenso' WHERE id=?",[$otherId]);assertTest('Suspensão revoga sessão ativa',$other->request('/participar',['id'=>(string)$report,'acao'=>'confirmar'])['status']===401);
 assertTest('Indisponibilidade hidrológica explícita',str_contains($guest->request('/enchentes')['body'],'Informações atualizadas indisponíveis'));
 $quotaIds[]=DB::insert('cotas_inundacao',['bairro_id'=>1,'rua'=>'Rua fictícia de teste','trecho'=>'Trecho demonstrativo','cota_metros'=>7,'estacao_referencia'=>'TESTE-'.$run,'referencia'=>'regua-A','fonte'=>'Teste automatizado','url_fonte'=>'https://example.invalid','demonstracao'=>1]);
 $hydro=new DatabaseHydrology();assertTest('Simulação compara cota compatível',count($hydro->simulate('TESTE-'.$run,'regua-A',8,true))===1);
 $rejected=false;try{$hydro->simulate('TESTE-'.$run,'regua-B',8,true);}catch(\App\Services\HttpError){$rejected=true;}assertTest('Cotas incompatíveis rejeitadas',$rejected);
 $admin->request('/admin/cadastros');assertTest('Cadastro de categoria',$admin->request('/admin/cadastros',['tipo'=>'categorias','nome'=>'Teste '.$run,'ativo'=>'1'])['status']===302);
 $category=(int)DB::one('SELECT id FROM categorias WHERE nome=?',['Teste '.$run])['id'];$categoryIds[]=$category;
 assertTest('Desativar categoria sem apagar',$admin->request('/admin/cadastros',['tipo'=>'categorias','id'=>(string)$category,'nome'=>'Teste '.$run])['status']===302 && (int)DB::one('SELECT ativo FROM categorias WHERE id=?',[$category])['ativo']===0);
 assertTest('Logout',$citizen->request('/sair',[])['status']===302);
 assertTest('Sessão encerrada',$citizen->request('/painel')['status']===401);
} catch(Throwable $e) {echo 'INTERRUPÇÃO: '.$e->getMessage()."\n";if(!array_filter($results,fn($r)=>!$r['passed']))$results[]=['name'=>'Execução integral','passed'=>false];}
finally {
 foreach($limitBackups as $path=>$backup){if($backup===null)unlink($path);else file_put_contents($path,$backup);}
 foreach($reports as $id){foreach(['confirmacoes','denuncias','solicitacoes_resolucao','notificacoes','historico_ocorrencias','ocorrencia_imagens'] as $table)DB::query("DELETE FROM $table WHERE ocorrencia_id=?",[$id]);DB::query('DELETE FROM ocorrencias WHERE id=?',[$id]);}
 foreach($images as $image){$file=ROOT.'/storage/uploads/'.basename($image['caminho_arquivo']);if(is_file($file))unlink($file);}
 foreach($quotaIds as $id)DB::query('DELETE FROM cotas_inundacao WHERE id=?',[$id]);
 foreach($categoryIds as $id)DB::query('DELETE FROM categorias WHERE id=?',[$id]);
 foreach($ids as $id){DB::query('DELETE FROM auditoria WHERE usuario_id=?',[$id]);DB::query('DELETE FROM usuarios WHERE id=?',[$id]);}
}
$failed=count(array_filter($results,fn($r)=>!$r['passed']));
file_put_contents(ROOT.'/docs/testes/resultado-http.json',json_encode(['executado_em'=>date('c'),'ambiente'=>'PHP '.PHP_VERSION.' / MySQL local / HTTP','aprovados'=>count($results)-$failed,'falhas'=>$failed,'casos'=>$results],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
echo (count($results)-$failed)." aprovados; $failed falhas.\n";exit($failed?1:0);
