// Usage: node tests/run.mjs <path-to-node_modules-containing-php-wasm> [8.3|7.4]
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';
import { File } from 'node:buffer';
globalThis.File ??= File;
const modules=process.argv[2];
const version=process.argv[3] || '8.3';
if(!modules)throw new Error('Pass the node_modules directory containing @php-wasm/universal and @php-wasm/node-'+version.replace('.','-'));
const { PHP, loadPHPRuntime }=await import(pathToFileURL(path.resolve(modules,'@php-wasm/universal/index.js')).href);
const { getPHPLoaderModule }=await import(pathToFileURL(path.resolve(modules,'@php-wasm/node-'+version.replace('.','-')+'/index.js')).href);
const root=path.resolve(path.dirname(fileURLToPath(import.meta.url)),'..');
const php=new PHP(await loadPHPRuntime(await getPHPLoaderModule()));
try {
 php.mkdir('/wordpress');
 php.mkdir('/wordpress/el-crisol-mantenimiento');
 php.mkdir('/wordpress/tests');
 for(const name of ['el-crisol-mantenimiento.php','template.php','uninstall.php']){
  php.writeFile('/wordpress/el-crisol-mantenimiento/'+name,fs.readFileSync(root+'/el-crisol-mantenimiento/'+name));
 }
 php.writeFile('/wordpress/tests/maintenance.php',fs.readFileSync(root+'/tests/maintenance.php'));
 const result=await php.run({scriptPath:'/wordpress/tests/maintenance.php'});
 process.stdout.write(result.text);
 if(result.errors)process.stderr.write(result.errors);
 if(result.exitCode!==0||result.errors)process.exitCode=1;
} finally { php.exit(); }
