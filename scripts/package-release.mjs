// Portable release packager: source + built frontend, with an explicit allowlist.
import fs from 'node:fs';
import path from 'node:path';
const destination=process.argv[2];
if(!destination)throw Error('Usage: node scripts/package-release.mjs ABSOLUTE_NEW_DIRECTORY');
if(!path.isAbsolute(destination)||fs.existsSync(destination))throw Error('Use a new absolute staging directory; existing data is never replaced.');
const root=process.cwd();
if(!fs.existsSync('public/build/manifest.json'))throw Error('Build frontend assets first.');
fs.mkdirSync(destination,{recursive:true});
const allowed=['app','bootstrap','config','database/migrations','database/seeders','public','resources/views','routes','scripts/configure-hosted.php','artisan','composer.json','composer.lock','.env.hosted.example','README.md'];
let files=0;let bytes=0;
function copy(source,target){const stat=fs.lstatSync(source);if(stat.isSymbolicLink())throw Error('Unexpected symlink: '+source);if(stat.isDirectory()){
 fs.mkdirSync(target,{recursive:true});for(const name of fs.readdirSync(source)){if(name==='.gitignore'||name==='.DS_Store')continue;
 const relative=path.relative(root,path.join(source,name)).replaceAll('\\','/');if(relative.startsWith('bootstrap/cache/')||relative==='public/hot'||relative==='public/storage')continue;copy(path.join(source,name),path.join(target,name));}
}else{fs.mkdirSync(path.dirname(target),{recursive:true});fs.copyFileSync(source,target);files++;bytes+=stat.size;}}
for(const item of allowed)copy(path.join(root,item),path.join(destination,item));
for(const dir of ['bootstrap/cache','storage/app/private','storage/app/public','storage/framework/cache/data','storage/framework/sessions','storage/framework/views','storage/logs'])fs.mkdirSync(path.join(destination,dir),{recursive:true});
fs.writeFileSync(path.join(destination,'RELEASE.json'),JSON.stringify({application:'ShipFast',preparedAt:new Date().toISOString(),files,bytes,excludes:['local .env','local database','user records','password hashes','logs','sessions','SSH credentials','node_modules','development dependencies'],requires:'Install production PHP dependencies from composer.lock on hosting'},null,2));
console.log(JSON.stringify({directory:destination,files,bytes}));
