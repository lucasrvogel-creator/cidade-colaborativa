// Executa apenas sobre uma cópia local do arquivo público; nunca executa JavaScript remoto.
import {readFile,writeFile,mkdir} from 'node:fs/promises';
import {createHash} from 'node:crypto';
const source=await readFile('storage/defesa-civil-public.js','utf8');
const start=source.indexOf('const ao=['), end=source.indexOf(']',start);
if(start<0||end<0)throw new Error('Estrutura da fonte mudou; revisão manual necessária.');
const block=source.slice(start,end), rows=[];
for(const match of block.matchAll(/\{name:`([^`]+)`,min:(null|[\d.]+),max:(null|[\d.]+)(,maxOpen:!0)?\}/g)) {
  const [,street,min,max,open]=match;
  rows.push({rua:street,minima:min==='null'?null:Number(min),maxima:max==='null'?null:Number(max),maxima_aberta:!!open});
}
if(rows.length!==555||new Set(rows.map(x=>x.rua)).size!==555)throw new Error('A contagem não confere com a tabela pública observada (555 ruas).');
const data={fonte:'Defesa Civil de Rio do Sul — Planilha Inundação de Ruas (A–Z)',url:'https://defesacivil.riodosul.sc.gov.br/',arquivo_publico:'https://defesacivil.riodosul.sc.gov.br/assets/index-r2-BzuAJG6w.js',consultado_em:'2026-11-25',data_atualizacao_fonte:null,estacao:'Ponte Dom Tito Buss',referencia:'Régua da Ponte Dom Tito Buss',bairro:'Não informado na tabela de origem',sha256_arquivo:createHash('sha256').update(source).digest('hex'),observacao:'20,00+ é limite aberto acima de 20 m, não uma cota exata de 20 m. Mínima ausente permanece nula. Não é leitura em tempo real.',registros:rows};
await mkdir('database/data',{recursive:true});await writeFile('database/data/cotas-defesa-civil.json',JSON.stringify(data,null,2)+'\n');
console.log(`${rows.length} cotas extraídas; mínimas ausentes: ${rows.filter(r=>r.minima===null).length}.`);
