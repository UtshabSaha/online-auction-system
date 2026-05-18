function qs(id){return document.getElementById(id)}
function escapeHtml(value){return String(value ?? '').replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c];});}
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
    postAjax('public/api/place_bid.php',{listing_id:listingId,amount:qs('bidAmount').value},function(res){
        qs('bidMessage').innerText=res.message;
        if(res.success){ qs('currentBid').innerText=res.current_bid; refreshBids(listingId); }
    });
}
function setAutoBid(listingId){
    postAjax('public/api/auto_bid.php',{listing_id:listingId,max_amount:qs('autoBidAmount').value},function(res){
        qs('autoBidMessage').innerText=res.message;
        if(res.success){
            if(qs('currentBid') && res.current_bid) qs('currentBid').innerText=res.current_bid;
            refreshBids(listingId);
        }
    });
}
function toggleWatch(listingId){
    postAjax('public/api/watchlist_toggle.php',{listing_id:listingId},function(res){alert(res.message);});
}
function refreshBids(listingId){
    getAjax('public/api/live_bid_history.php?listing_id='+listingId,function(res){
        if(!res.success||!qs('bidHistory'))return;
        qs('bidHistory').innerHTML=res.data.map(b=>'<tr><td>'+escapeHtml(b.buyer_name)+'</td><td>'+escapeHtml(b.amount)+'</td><td>'+(Number(b.is_auto_bid) ? 'Auto' : 'Manual')+'</td><td>'+escapeHtml(b.created_at)+'</td></tr>').join('');
        if(qs('currentBid')) qs('currentBid').innerText=res.current_bid;
        if(qs('bidCount')) qs('bidCount').innerText=res.bid_count;
    });
}
function listingCard(l){
    const img = l.image_path || 'assets/images/auction-placeholder.png';
    return '<div class="card auction-card"><img src="'+escapeHtml(img)+'" alt=""><h3>'+escapeHtml(l.title)+'</h3><p>'+escapeHtml(l.category_name)+' - '+escapeHtml(l.condition)+'</p><p><strong>Current bid:</strong> '+escapeHtml(l.current_bid)+'</p><p><strong>Time left:</strong> <span class="countdown" data-countdown="'+escapeHtml(l.end_datetime)+'">'+escapeHtml(l.end_datetime)+'</span></p><a class="btn" href="index.php?page=auction&id='+escapeHtml(l.id)+'">View Auction</a></div>';
}
function searchAuctions(){
    const params=new URLSearchParams({q:qs('q').value,category:qs('category').value,condition:qs('condition').value,min:qs('min').value,max:qs('max').value,time:qs('time').value});
    getAjax('public/api/search_listings.php?'+params.toString(),function(res){
        if(!res.success)return; qs('auctionGrid').innerHTML=res.data.map(listingCard).join(''); updateCountdowns();
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
function formatCountdown(endTime){
    const end = new Date(String(endTime).replace(' ', 'T')).getTime();
    const diff = end - Date.now();
    if(!end || diff <= 0) return 'Ended';
    const days = Math.floor(diff / 86400000);
    const hours = Math.floor((diff % 86400000) / 3600000);
    const minutes = Math.floor((diff % 3600000) / 60000);
    const seconds = Math.floor((diff % 60000) / 1000);
    if(days > 0) return days+'d '+hours+'h '+minutes+'m';
    return hours+'h '+minutes+'m '+seconds+'s';
}
function updateCountdowns(){
    document.querySelectorAll('[data-countdown]').forEach(function(el){ el.innerText = formatCountdown(el.dataset.countdown); });
}
function initCreateListingAjax(){
    const form = qs('createListingForm');
    if(!form) return;
    const message = qs('createListingMessage');
    const button = qs('createListingButton');
    form.addEventListener('submit', function(event){
        event.preventDefault();
        if(button){ button.disabled = true; button.innerText = 'Submitting...'; }
        fetch(form.action || window.location.href, {
            method: 'POST',
            body: new FormData(form),
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        }).then(function(response){ return response.json(); }).then(function(res){
            if(message){ message.innerHTML = '<div class="alert '+(res.success ? 'success-msg' : 'error')+'">'+escapeHtml(res.message || '')+'</div>'; }
            if(res.success){ form.reset(); }
        }).catch(function(){
            if(message){ message.innerHTML = '<div class="alert error">Could not submit listing. Please try again.</div>'; }
        }).finally(function(){
            if(button){ button.disabled = false; button.innerText = 'Create Listing'; }
        });
    });
}
setInterval(updateCountdowns, 1000);
updateCountdowns();
initCreateListingAjax();
setInterval(function(){ document.querySelectorAll('[data-refresh-bids]').forEach(el=>refreshBids(el.dataset.refreshBids)); }, 5000);
