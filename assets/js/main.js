function qs(id){return document.getElementById(id)}
function postAjax(url, data, callback){
    const xhr=new XMLHttpRequest();
    xhr.open('POST',url,true);
    xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    xhr.onload=function(){try{callback(JSON.parse(xhr.responseText));}catch(e){callback({success:false,message:'Invalid server response'});}};
    xhr.send(new URLSearchParams(data).toString());
}
function getAjax(url, callback){
    const xhr=new XMLHttpRequest(); xhr.open('GET',url,true);
    xhr.onload=function(){try{callback(JSON.parse(xhr.responseText));}catch(e){callback({success:false,message:'Invalid server response'});}}; xhr.send();
}
function placeBid(listingId){
    postAjax('public/api/buyer.php?action=place_bid',{listing_id:listingId,amount:qs('bidAmount').value},function(res){
        qs('bidMessage').innerText=res.message;
        if(res.success){ qs('currentBid').innerText=res.current_bid; refreshBids(listingId); }
    });
}
function setAutoBid(listingId){
    postAjax('public/api/buyer.php?action=auto_bid',{listing_id:listingId,max_amount:qs('autoBidAmount').value},function(res){qs('autoBidMessage').innerText=res.message;});
}
function toggleWatch(listingId){
    postAjax('public/api/buyer.php?action=watchlist',{listing_id:listingId},function(res){alert(res.message);});
}
function refreshBids(listingId){
    getAjax('public/api/buyer.php?action=bid_history&listing_id='+listingId,function(res){
        if(!res.success||!qs('bidHistory'))return;
        qs('bidHistory').innerHTML=res.bids.map(b=>'<tr><td>'+b.buyer_name+'</td><td>'+b.amount+'</td><td>'+b.created_at+'</td></tr>').join('');
        if(qs('currentBid')) qs('currentBid').innerText=res.current_bid;
    });
}
function searchAuctions(){
    const params=new URLSearchParams({q:qs('q').value,category:qs('category').value,condition:qs('condition').value,min:qs('min').value,max:qs('max').value});
    getAjax('public/api/buyer.php?action=search&'+params.toString(),function(res){
        if(!res.success)return; qs('auctionGrid').innerHTML=res.html;
    });
}
function moderateListing(id,status){
    const reason=prompt('Reason or note:', '') || '';
    postAjax('public/api/moderator.php?action=listing_status',{listing_id:id,status:status,reason:reason},function(res){alert(res.message); if(res.success) location.reload();});
}
function adminUserSearch(){
    getAjax('public/api/admin.php?action=search_users&q='+encodeURIComponent(qs('adminUserQ').value),function(res){
        if(!res.success)return; qs('adminUsersTable').innerHTML=res.html;
    });
}
setInterval(function(){ document.querySelectorAll('[data-refresh-bids]').forEach(el=>refreshBids(el.dataset.refreshBids)); }, 5000);
