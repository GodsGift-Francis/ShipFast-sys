const {test}=require('node:test');
const assert=require('node:assert/strict');
const vm=require('node:vm');
const fs=require('node:fs');
function form(fetch){
 let factory;
 const source=fs.readFileSync('resources/js/app.js','utf8').replace(/^import .*;\r?\n/gm,'');
 vm.runInNewContext(source,{Alpine:{data:(_,value)=>factory=value,start(){}},window:{},AbortController,Intl,Date,fetch,document:{querySelector:()=>({content:'test-csrf'})}});
 return factory({originCountry:'Nigeria',destinationCountry:'Ghana',weight:2});
}
test('failed estimate can be retried without changing parcel details',async()=>{
 let calls=0;
 const estimate=form(async()=>{calls++;if(calls===1)throw Error('Network unavailable');return {ok:true,json:async()=>({rates:{standard:{cost:12}},unavailable:{}})};});
 await estimate.refresh();assert.equal(estimate.error,'Network unavailable');
 await estimate.refresh();assert.equal(calls,2);assert.equal(estimate.estimate.cost,12);assert.equal(estimate.error,'');
});
test('superseded request failure cannot overwrite a newer successful estimate',async()=>{
 let rejectFirst;let calls=0;
 const estimate=form(async()=>{calls++;if(calls===1)return new Promise((_,reject)=>rejectFirst=reject);return {ok:true,json:async()=>({rates:{standard:{cost:20}},unavailable:{}})};});
 const first=estimate.refresh();estimate.weight=3;await estimate.refresh();rejectFirst(Error('Old request failed'));await first;
 assert.equal(estimate.error,'');assert.equal(estimate.estimate.cost,20);assert.equal(estimate.loading,false);
});
