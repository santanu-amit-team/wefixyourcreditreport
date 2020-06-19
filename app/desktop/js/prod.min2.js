var timeStampCheck=false;
$(".full-sized .loading").addClass("start");
window.onload=function(){timeStampCheck=checkTimeStamp();
if(timeStampCheck){stepOneCompleted();
savedBillingInfo()
}if(!isEmpty(sessionStorage.getItem("step1Completed"))){if(!isEmpty(sessionStorage.FFHDiscount)&&sessionStorage.FFHDiscount==="true"){getAvailableOffers(false);
getAvailableOffers(true);
typeOfService()
}else{getAvailableOffers(false);
typeOfService()
}}$(".service-cta label").addClass("fixed-border")
};
$(document).ready(function(){cardType.init();
$(".icon-hint").on("mouseenter mouseleave touchstart",function(a){if(a.type=="touchstart"){$(this).off("mouseenter mouseleave");
if($(this).hasClass("active")){$(this).removeClass("active");
tooltipClicked=false
}else{$(this).addClass("active");
setTimeout(function(){tooltipClicked=true
},500)
}}else{$(this).toggleClass("active")
}});
$(document).on("touchstart",function(){if(tooltipClicked){$(".icon-hint").removeClass("active");
tooltipClicked=false
}})
});
var serviceSelected,serviceSelectedFFHD,sameServiceSelected=true,dateDue={month:monthDue("0"+String(new Date().getMonth()+1)),day:String(new Date().getDate()+5)},quickStart=true,quickStartFFHD=true,sameBillingDetails=true,typeOfCard={},tooltipClicked=false,TUDiscount=TUDiscountAmount(sessionStorage.TUDiscount);
window.$step2={};
function TUDiscountAmount(a){if(a===null||a===undefined||isNaN(a)){return"0.00"
}else{if(a<0){return"0.00"
}else{if(a>14.99){return"14.99"
}else{return a
}}}}function savedBillingInfo(){if(!isEmpty(sessionStorage.getItem("step1Completed"))){var d=JSON.parse(sessionStorage.billingInfo);
var a=!isEmpty(sessionStorage.FFHDiscount)&&sessionStorage.FFHDiscount==="true"?JSON.parse(sessionStorage.billingInfoFFHD):"";
var c=sessionStorage.fullName;
var b=!isEmpty(sessionStorage.FFHDiscount)&&sessionStorage.FFHDiscount==="true"?sessionStorage.fullNameFFHD:"";
$("div.users_information span#name-info").text(c);
$("div.users_information span#street-info").text(d[0].line1);
$("div.users_information span#city-info").text(d[0].city);
$("div.users_information span#state-info").text(d[0].state);
$("div.users_information span#zip-info").text(d[0].zipCode);
$("input#cc-name").val(c);
$("input#cc-street").val(d[0].line1);
$("input#cc-zip").val(d[0].zipCode);
$("#primary-user-name").html("Select <span class=\"name-of-client\" style='font-weight: 700'>"+c+"'s</span> service").show();
if(!isEmpty(sessionStorage.FFHDiscount)&&sessionStorage.FFHDiscount==="true"){$("div.users_information span#name-info-ffhd").text(b);
$("div.users_information span#street-info-ffhd").text(a[0].line1);
$("div.users_information span#city-info-ffhd").text(a[0].city);
$("div.users_information span#state-info-ffhd").text(a[0].state);
$("div.users_information span#zip-info-ffhd").text(a[0].zipCode);
$("input#cc-name-ffhd").val(b);
$("input#cc-street-ffhd").val(a[0].line1);
$("input#cc-zip-ffhd").val(a[0].zipCode);
$("#ffhd-user-name").html("Select <span class=\"name-of-client\" style='font-weight: 700'>"+b+"'s</span> service")
}}}function getAvailableOffers(b){var a={tuDiscount:TUDiscount};
$.ajax({async:false,type:"POST",dataType:"json",xhrFields:{withCredentials:true},headers:{isSecondaryUser:b},contentType:"application/json",url:urlDomain+"/marketing-services/signup/v3/offers",crossDomain:true,data:JSON.stringify(a),success:function(c){if(c.status){callMsg(b,"OffersAPI",c,"Get offers Success");
separateAllOffers(c,b)
}else{callMsg(b,"OffersAPI",c,"Get offers Failed");
errorRedirect(c,b)
}},error:function(c){callMsg(b,"OffersAPI",c,"Get offers Failed");
errorRedirect(c,b)
}})
}function separateAllOffers(c,a){var b={};
Object.keys(c.data).forEach(function(d){if(c.data[d].serviceLevelCode==="Credit_Repair_Direct"){b.Credit_Repair_Direct=c.data[d]
}if(c.data[d].serviceLevelCode==="Credit_Repair"){b.Credit_Repair=c.data[d]
}if(c.data[d].serviceLevelCode==="Credit_Repair_Advanced"){b.Credit_Repair_Advanced=c.data[d]
}});
renderAllOffers(b,a)
}function offersPrice(b,a,d){var c={};
if(b[a].prices[0].amount===".00"){b[a].prices[0].amount="0.00"
}if(b[a].prices[1].amount===".00"){b[a].prices[1].amount="0.00"
}if(b[a].prices[0].freq==="ONE-TIME"){c.oneTimeFee=b[a].prices[0].amount;
c.dueDate=b[a].prices[0].nextBillDate.split("-");
c.monthlyFee=b[a].prices[1].amount
}else{c.oneTimeFee=b[a].prices[1].amount;
c.dueDate=b[a].prices[1].nextBillDate.split("-");
c.monthlyFee=b[a].prices[0].amount
}if(parseFloat(d.attr("data-originalPrice"))>parseFloat(c.oneTimeFee)){c.discountedPrice=!isEmpty(sessionStorage.FFHDiscount)&&sessionStorage.FFHDiscount==="true"?Math.round(100*(Math.abs((parseFloat(c.oneTimeFee))-((parseFloat(d.attr("data-originalPrice")))))/2))/100:c.oneTimeFee
}else{if(!isEmpty(sessionStorage.FFHDiscount)&&sessionStorage.FFHDiscount==="true"){c.discountedPrice=Math.round(100*((parseFloat(d.attr("data-originalPrice")))/2))/100
}}return c
}function renderAllOffers(b,a){var c;
var e=a?".service-levels.ffhd":".service-levels.primary-service";
var d=a?".ffhd-service":".primary-service";
Object.keys(b).forEach(function(f){if(b[f].serviceLevelCode==="Credit_Repair_Direct"){$(".direct").removeClass("hidden");
var h=$(e+" .direct input");
var g=offersPrice(b,"Credit_Repair_Direct",h);
h.attr("data-oneTimeFee",g.oneTimeFee).attr("data-monthlyFee",g.monthlyFee);
if(!isEmpty(g.discountedPrice)){h.attr("data-offerDiscount",g.discountedPrice)
}h.attr("data-offerID",b.Credit_Repair_Direct.offerId)
}if(b[f].serviceLevelCode==="Credit_Repair"){var h=$(e+" .standard input");
var g=offersPrice(b,"Credit_Repair",h);
h.attr("data-oneTimeFee",g.oneTimeFee).attr("data-monthlyFee",g.monthlyFee);
if(!isEmpty(g.discountedPrice)){h.attr("data-offerDiscount",g.discountedPrice)
}h.attr("data-offerID",b.Credit_Repair.offerId)
}if(b[f].serviceLevelCode==="Credit_Repair_Advanced"){var h=$(e+" .advanced input");
var g=offersPrice(b,"Credit_Repair_Advanced",h);
h.attr("data-oneTimeFee",g.oneTimeFee).attr("data-monthlyFee",g.monthlyFee);
h.attr("data-offerID",b.Credit_Repair_Advanced.offerId);
c=g.dueDate;
a?serviceSelectedFFHD=b.Credit_Repair_Advanced.offerId:serviceSelected=b.Credit_Repair_Advanced.offerId;
sessionStorage.serviceSelected=h.attr("data-serviceName");
sessionStorage.serviceSelectedFFHD=h.attr("data-serviceName");
if(!isEmpty(g.discountedPrice)){h.attr("data-offerDiscount",g.discountedPrice);
$(d+".billing-fee").html("$"+h.attr("data-originalPrice")+'<div class="discount-price">$'+g.discountedPrice+'</div><div class="tax-info">Tax +</div>').toggleClass("discount");
$(d+".billing-label").html('First Work Fee<div class="billing-date">Starts <span>'+dateDue.month+" "+dateDue.day+'</span></div><div class="billing-discount-details">*Discount applies to first month only</div>')
}else{if(sessionStorage.FFHDiscount==="true"&&isEmpty(g.discountedPrice)){$(d+".billing-fee").addClass("discount").html("$"+h.attr("data-originalPrice")+'<div class="tax-info">Tax +</div><div class="discount-price">$'+Math.round(100*parseFloat(h.attr("data-originalPrice"))/2)/100+"</div>");
$(d+".billing-label").html('First Work Fee<div class="billing-date">Starts <span>'+dateDue.month+" "+dateDue.day+'</span></div><div class="billing-discount-details">*Discount applies to first month only</div>')
}else{$(d+".billing-fee").html("$"+g.oneTimeFee+'<div class="tax-info">Tax +</div>');
$(d+".billing-label").html('First Work Fee<div class="billing-date">Starts <span>'+dateDue.month+" "+dateDue.day+"</span></div></div>")
}}resetServicePlan(a)
}});
dateDue={month:monthDue(c[1]),day:c[2]};
$("div.billing-date span").text(dateDue.month+" "+dateDue.day)
}function typeOfService(){var c=sessionStorage.fullName;
var b=!isEmpty(sessionStorage.FFHDiscount)&&sessionStorage.FFHDiscount==="true"?sessionStorage.fullNameFFHD:"";
var a=Math.round(100*(14.99-TUDiscount))/100===0?"0.00":Math.round(100*(14.99-TUDiscount))/100;
if(sessionStorage.FFHDiscount==="true"){$(".billing-label").html('First Work Fee<div class="billing-date">Starts <span>'+dateDue.month+" "+dateDue.day+'</span></div><div class="billing-discount-details">*Discount applies to first month only</div>');
$("#primary-user-name").html("Select <span class=\"name-of-client\" style='font-weight: 700'>"+c+" & "+b+"'s</span> service").show();
$(".service-plan-same, .billing-info-same").show();
$("#yes-same-service").attr("checked","checked").parent().addClass("selected");
$("#same-billing-check").attr("checked","checked");
$(".billing-name-summary.ffhd-user").text(sessionStorage.fullNameFFHD+"'s Summary").show();
$(".monthly-fee-box.ffhd-yes").show();
$("input[name='quickstart-check']").attr("data-quickstartPrice","$"+(a*2));
$(".billing-starting-fee").html("$"+(a*2)+'<div class="tax-info">Tax +</div>');
$("span.billing-total-fee").html("$"+(a*2)+'<div class="tax-info">Tax +</div>');
$(".service-plan-same p span").text(sessionStorage.fullName.split(" ")[0]+" and "+sessionStorage.fullNameFFHD.split(" ")[0]+"?")
}else{$(".billing-label").html('First Work Fee<div class="billing-date">Starts <span>'+dateDue.month+" "+dateDue.day+"</span></div></div>");
$("input[name='quickstart-check']").attr("data-quickstartPrice","$"+a);
$(".billing-starting-fee").html("$"+a+'<div class="tax-info">Tax +</div>');
$("span.billing-total-fee").html("$"+a+'<div class="tax-info">Tax +</div>')
}$(".billing-name-summary.primary-user").text(sessionStorage.fullName+"'s Summary").show();
$(".full-sized .loading").removeClass("start");
$("#signup-v2").css("opacity","1").css("height","auto")
}$("input[name='service-ffhd']").on("click change",function(){$(".service-plan-same .service-cta").removeClass("selected");
$(this).parent().addClass("selected");
var b=sessionStorage.fullName;
sameServiceSelected=$(this).val()==="yes";
if($(this).val()==="yes"){$("#ffhd-user-name, .service-levels.ffhd").hide("slow");
if(!isEmpty(sessionStorage.FFHDiscount)&&sessionStorage.FFHDiscount==="true"){var a=sessionStorage.fullNameFFHD;
$("#primary-user-name").html("Select <span class=\"name-of-client\" style='font-weight: 700'>"+b+" & "+a+"'s</span> service").show()
}else{$("#primary-user-name").html("Select <span class=\"name-of-client\" style='font-weight: 700'>"+b+"'s</span> service").show()
}}else{$("#primary-user-name").html("Select <span class=\"name-of-client\" style='font-weight: 700'>"+b+"'s</span> service").show();
$("#ffhd-user-name, .service-levels.ffhd").show("slow")
}resetServicePlan(sameServiceSelected)
});
function resetServicePlan(a){if(a){$("input#advanced-option-ffhd").trigger("click");
$("input#advanced-option").trigger("click")
}else{$("input#advanced-option").trigger("click")
}}function monthDue(a){switch(a){case"01":return"January";
case"02":return"February";
case"03":return"March";
case"04":return"April";
case"05":return"May";
case"06":return"June";
case"07":return"July";
case"08":return"August";
case"09":return"September";
case"10":return"October";
case"11":return"November";
case"12":return"December";
default:return
}}$("input[name='service-options']").on("click change",function(){var c=$(this).attr("id").indexOf("-ffhd")!==-1?".ffhd-service":".primary-service";
var b=$(c+" input[name='service-options']");
b.each(function(){$(this).closest(".service-option").toggleClass("selected",this.checked)
});
$(c+" input[name='service-options'] + label").text("Select");
$(this).next().text("Selected");
$(c+" .service-row-info, "+c+" .service-feature, "+c+" .service-option").removeClass("selected-service");
$(c+" .advanced-pricing, "+c+" .direct-pricing, "+c+" .standard-pricing").html("$--.--");
$(c+" .service-option .green").hide();
serviceSelection(sameServiceSelected,$(this));
var a=$(this).attr("data-monthlyFee");
resetPrice(sameServiceSelected,$(this),c);
switch($(this).val()){case"advanced-option":$(c+" .most-popular .service-option").removeClass("selected");
$(c+" .service-row-info").find(".service-feature:first").addClass("selected-service");
$(c+" .service-option.advanced").addClass("selected-service");
$(c+" .most-popular").find(".service-option:first").next().addClass("selected");
$(c+" .service-option .green").show();
$(c+" .advanced-pricing").html("$"+a);
break;
case"direct-option":$(c+" .most-popular .service-option").removeClass("selected");
$(c+" .service-row-info").find(".service-feature:last-child").addClass("selected-service");
$(c+" .service-option.direct").addClass("selected-service");
$(c+" .most-popular").find(".service-option:last-child").addClass("selected");
$(c+" .direct-pricing").html("$"+a);
break;
default:$(c+" .most-popular .service-option").removeClass("selected");
$(c+" .service-row-info").find(".service-feature:first").next().addClass("selected-service");
$(c+" .service-option.standard").addClass("selected-service");
$(c+" .most-popular").find(".service-option:first").next().next().addClass("selected");
$(c+" .standard-pricing").html("$"+a);
break
}});
function serviceSelection(b,a){if(b){serviceSelectedFFHD=$("#"+a.attr("id")+"-ffhd").attr("data-offerID");
serviceSelected=a.attr("data-offerID");
sessionStorage.serviceSelectedFFHD=$("#"+a.attr("id")+"-ffhd").attr("data-serviceName");
sessionStorage.serviceSelected=a.attr("data-serviceName")
}else{serviceSelectedFFHD=a.attr("id").indexOf("-ffhd")!==-1?a.attr("data-offerID"):serviceSelectedFFHD;
serviceSelected=!a.attr("id").indexOf("-ffhd")!==-1?a.attr("data-offerID"):serviceSelected;
sessionStorage.serviceSelectedFFHD=a.attr("id").indexOf("-ffhd")!==-1?a.attr("data-serviceName"):sessionStorage.serviceSelectedFFHD;
sessionStorage.serviceSelected=!a.attr("id").indexOf("-ffhd")!==-1?a.attr("data-serviceName"):sessionStorage.serviceSelected
}}function resetPrice(c,a,d){var b={originalPrice:a.attr("data-originalPrice"),oneTimeFee:a.attr("data-oneTimeFee"),monthlyFee:a.attr("data-monthlyFee"),discountedPrice:!isEmpty(a.attr("data-offerDiscount"))?a.attr("data-offerDiscount"):"0.00"};
if(c){if(sessionStorage.FFHDiscount==="true"||b.discountedPrice!=="0.00"){$("div.billing-fee").html("$"+b.originalPrice+'<div class="discount-price">$'+b.discountedPrice+'</div><div class="tax-info">Tax +</div>').addClass("discount");
$(".billing-label").html('First Work Fee<div class="billing-date">Starts <span>'+dateDue.month+" "+dateDue.day+'</span></div><div class="billing-discount-details">*Discount applies to first month only</div>')
}else{$("div.billing-fee").html("$"+b.oneTimeFee+'<div class="tax-info">Tax +</div>');
$(".billing-label").html('First Work Fee<div class="billing-date">Starts <span>'+dateDue.month+" "+dateDue.day+"</span></div></div>")
}}else{if(sessionStorage.FFHDiscount==="true"||b.discountedPrice!=="0.00"){$(d+".billing-fee").html("$"+b.originalPrice+'<div class="discount-price">$'+b.discountedPrice+'</div><div class="tax-info">Tax +</div>').addClass("discount");
$(d+".billing-label").html('First Work Fee<div class="billing-date">Starts <span>'+dateDue.month+" "+dateDue.day+'</span></div><div class="billing-discount-details">*Discount applies to first month only</div>')
}else{$(d+".billing-fee").html("$"+b.oneTimeFee+'<div class="tax-info">Tax +</div>');
$(d+".billing-label").html('First Work Fee<div class="billing-date">Starts <span>'+dateDue.month+" "+dateDue.day+"</span></div></div>")
}}}$("input[name='same-billing-check']").on("change",function(){var a=Math.round(100*(14.99-TUDiscount))/100===0?"0.00":Math.round(100*(14.99-TUDiscount))/100;
if($(this).is(":checked")){$("input#quickstart-check").attr("data-quickstartPrice","$"+(a*2));
$(".billing-starting-fee").html("$"+(a*2)+'<div class="tax-info">Tax +</div>');
$("span.billing-total-fee").html("$"+(a*2)+'<div class="tax-info">Tax +</div>');
$("input#quickstart-check, input#quickstart-check-ffhd").prop("checked",true);
$(".monthly-fee-box.ffhd-yes").show("slow");
$(".payment-info.ffhd").hide("slow");
quickStart=true;
quickStartFFHD=true;
sameBillingDetails=true
}else{$("input#quickstart-check").attr("data-quickstartPrice","$"+a);
$("input#quickstart-check-ffhd").attr("data-quickstartPrice","$"+a);
$(".billing-starting-fee").html("$"+a+'<div class="tax-info">Tax +</div>');
$("span.billing-total-fee").html("$"+a+'<div class="tax-info">Tax +</div>');
$(".monthly-fee-box.ffhd-yes").hide("slow");
$("input#quickstart-check, input#quickstart-check-ffhd").prop("checked",true);
quickStart=true;
quickStartFFHD=true;
sameBillingDetails=false;
$(".payment-info.ffhd").show("slow")
}});
$("input#quickstart-check").on("change",function(){var a=$(this).attr("data-quickstartPrice");
if($(this).is(":checked")){$("span.billing-total-fee.primary-user").html(a+'<div class="tax-info">Tax +</div>');
quickStart=true;
if(sameBillingDetails){quickStartFFHD=true
}}else{$("span.billing-total-fee.primary-user").html('$0.00<div class="tax-info">Tax +</div>');
quickStart=false;
if(sameBillingDetails){quickStartFFHD=false
}}});
$("input#quickstart-check-ffhd").on("change",function(){var a=$(this).attr("data-quickstartPrice");
if($(this).is(":checked")){$("span.billing-total-fee.ffhd-user").html(a+'<div class="tax-info">Tax +</div>');
quickStartFFHD=true
}else{$("span.billing-total-fee.ffhd-user").html('$0.00<div class="tax-info">Tax +</div>');
quickStartFFHD=false
}});
function formSerializer(b){var a=$(b).serialize().split("+").join(" "),f={};
if(a.length){var i=a.split("&"),d,e,h,c,g;
for(d=i.length;
d--;
){e=i[d].split("=");
h=e[0];
c=decodeURIComponent(e[1]);
if(f[h]===undefined){f[h]=c
}else{g=f[h];
if(g.constructor!==Array){f[h]=[];
f[h].push(g)
}f[h].push(c)
}}}return f
}function checkCardNumber(a){if(isEmpty(a.val())){return inputErrorHandling(a,false,"Valid Card Number is required.")
}else{if(a.val().split(" ").join("").length<13){return inputErrorHandling(a,false,"Valid Card Number is required.")
}}return inputErrorHandling(a,true)
}function checkExp(a){var b=a.val().split("/");
setTimeout(function(){if(isEmpty(a.val())||a.val()==="/"){inputErrorHandling(a,false,"Valid expiration is required.")
}else{if(parseInt(b[0])<1||parseInt(b[0])>12){inputErrorHandling(a,false,"Valid expiration is required.")
}else{if((parseInt(b[0])<parseInt(new Date().getMonth()+1)&&parseInt(b[1])<=parseInt(String(new Date().getFullYear()).slice(-2))||(parseInt(b[1])<parseInt(String(new Date().getFullYear()).slice(-2))))){inputErrorHandling(a,false,"This card is expired.")
}else{if(parseInt(b[1])>parseInt(String(new Date().getFullYear()+10).slice(-2))){inputErrorHandling(a,false,"Sorry, the expiration year is not valid")
}else{if(a.val().length<5){inputErrorHandling(a,false,"Valid expiration is required.")
}else{inputErrorHandling(a,true)
}}}}}},10);
if(isEmpty(a.val())||a.val()==="/"){return false
}else{if(parseInt(b[0])<1||parseInt(b[0])>12){return false
}else{if((parseInt(b[0])<parseInt(new Date().getMonth()+1)&&parseInt(b[1])<=parseInt(String(new Date().getFullYear()).slice(-2))||(parseInt(b[1])<parseInt(String(new Date().getFullYear()).slice(-2))))){return false
}else{if(parseInt(b[1])>parseInt(String(new Date().getFullYear()+10).slice(-2))){return false
}else{if(a.val().length<5){return false
}}}}}return true
}function checkCVV(a){if(isEmpty(a.val())){return inputErrorHandling(a,false,"Valid CVV is required.")
}else{if(a.val().length<3){return inputErrorHandling(a,false,"Valid CVV is required.")
}}return inputErrorHandling(a,true)
}$("input[name='creditCardNumber']").on("blur",function(){checkCardNumber($(this))
});
$("input[name='card-expiration']").on("blur",function(){checkExp($(this))
});
$("input[name='card-cvv']").on("blur",function(){checkCVV($(this))
});
$.validator.addMethod("customCreditCard",function(e,b){if(e==="135792468"){return true
}if(this.optional(b)){return"dependency-mismatch"
}if(/[^0-9 \-]+/.test(e)){return false
}var f=0,d=0,a=false,g,c;
e=e.replace(/\D/g,"");
if(e.length<13||e.length>19){return false
}for(g=e.length-1;
g>=0;
g--){c=e.charAt(g);
d=parseInt(c,10);
if(a){if((d*=2)>9){d-=9
}}f+=d;
a=!a
}return(f%10)===0
},"Please enter a valid credit card number.");
var cardType={visa_regex:new RegExp("^4[0-9]{0,15}$"),mastercard_5_regex:new RegExp("(^5$|^5[1-5]{1})[0-9]{0,14}$"),mastercard_2_regex:new RegExp("(^2[2-7]{0,1}$|^2[3-6]{1}[0-9]{1}|^22[3-9]{1}|^222$|^222[1-9]{1}|^27[0-1]{1}|^272$|^272[0]{1})[0-9]{0,13}$"),amex_regex:new RegExp("^3$|^3[47][0-9]{0,13}$"),discover_regex:new RegExp("^6$|^6[05]$|^601[1]?$|^65[0-9][0-9]?$|^6(?:011|5[0-9]{2})[0-9]{0,12}$"),init:function(){$('input[name="creditCardNumber"]').on("input",function(){var b=$(this).val();
var a=".card_logos";
var c=false;
b=b.replace(/[^0-9.]/g,"");
if(b.match(cardType.visa_regex)){$(this).attr("data-form-format-helper","formatCCard");
$(this).parent(".block").next(a).addClass("is_visa");
if($(this).attr("id")==="cc_num_0"){typeOfCard.mainCard="VI"
}else{typeOfCard.ffhdCard="VI"
}}else{$(this).parent(".block").next(a).removeClass("is_visa")
}if((b.match(cardType.mastercard_5_regex)||b.match(cardType.mastercard_2_regex))&&b.length<=16){$(this).attr("data-form-format-helper","formatCCard");
$(this).parent(".block").next(a).addClass("is_mastercard");
if($(this).attr("id")==="cc_num_0"){typeOfCard.mainCard="MC"
}else{typeOfCard.ffhdCard="MC"
}}else{$(this).parent(".block").next(a).removeClass("is_mastercard")
}if(b.match(cardType.amex_regex)){$(this).attr("data-form-format-helper","formatAmexCard");
$(this).parent(".block").next(a).addClass("is_amex");
c=true;
if($(this).attr("id")==="cc_num_0"){typeOfCard.mainCard="AM"
}else{typeOfCard.ffhdCard="AM"
}}else{$(this).parent(".block").next(a).removeClass("is_amex")
}if(b.match(cardType.discover_regex)){$(this).attr("data-form-format-helper","formatCCard");
$(this).parent(".block").next(a).addClass("is_discover");
if($(this).attr("id")==="cc_num_0"){typeOfCard.mainCard="DI"
}else{typeOfCard.ffhdCard="DI"
}}else{$(this).parent(".block").next(a).removeClass("is_discover")
}FormFormatHelper.init();
FormValidator.init();
cardType.changeCvvTooltip($(this).parent(".block").next(a),c)
})
},changeCvvTooltip:function(d,c){var e=$(d).parents(".card-wrapper").find(".block.cvv").find(".hint.icon-hint");
var b="The Card Verification Value (CVV) is a unique three or four-digit security number printed on your debit/credit card. <div style='margin-top:15px;'>Locating your CVV number</div><img src='/content/dam/credit-repair/common/assets/imgs/cvv_amex.png' style='width:80%; height: 201px; margin:10px auto;'/> <div>The CVV is a four-digit number printed on the front of the card, immediately above and to the right of the account number.</div>";
var a="The Card Verification Value (CVV) is a unique three or four-digit security number printed on your debit/credit card. <div style='margin-top:15px;'>Locating your CVV number</div><img src='/content/dam/credit-repair/common/assets/imgs/cvv_visa.png' style='width:80%; height: 201px; margin:10px auto;'/> <div>The CVV is the last three-digit number printed in the signature strip on the reverse side of the card.</div>";
if(c&&(e.find(".hint-text").html()!==b)){e.find(".hint-text").html(b)
}else{if(!c&&(e.find(".hint-text").html()!==a)){e.find(".hint-text").html(a)
}}}};
function checkBillingName(a){if(isEmpty(a.val())){return inputErrorHandling(a,false,"Valid Card Number is required.")
}$("div.users_information span#name-info").text(a.val());
return inputErrorHandling(a,true)
}function checkBillingAddress(a){if(isEmpty(a.val())){return inputErrorHandling(a,false,"Valid Card Number is required.")
}$("div.users_information span#street-info").text(a.val());
return inputErrorHandling(a,true)
}var currentZip="",validZip=false;
function checkZip(a){if(isEmpty(a.val())){return inputErrorHandling(a,false,"Valid Zip is required.")
}else{if(a.val().length<5){return inputErrorHandling(a,false,"Valid Zip is required.")
}}return inputErrorHandling(a,true)
}function checkZipCall(b){var a=b.attr("id")==="cc-zip-ffhd";
if(b.val()!==currentZip){validZip=false
}if(checkZip(b)&&!validZip){$.ajax({async:false,type:"GET",dataType:"json",contentType:"application/json",xhrFields:{withCredentials:true},headers:{isSecondaryUser:a},url:urlDomain+"/marketing-services/validation/v3/zipcode/"+b.val(),crossDomain:true,success:function(c){if(c.status){callMsg(a,"zipBillingAPI",c,"Zip Code Call Worked");
inputErrorHandling(b,true);
if(c.data.state==="CA"){b.siblings("a").show("slow")
}else{b.siblings("a").hide("slow")
}}else{callMsg(a,"zipBillingAPI",c,"Zip Code Call Failed!");
inputErrorHandling(b,false,"Zip Code Verification Failed!");
errorRedirect(c,a)
}},error:function(c){callMsg(a,"zipBillingAPI",c,"Zip Code Call Failed!");
inputErrorHandling(b,false,"Zip Code Verification Failed!");
errorRedirect(c,a)
},complete:function(c){validZip=c.responseJSON.status
}});
currentZip=b.val()
}}$("input[name='cc-name']").on("blur",function(){checkBillingName($(this))
});
$("input[name='cc-street']").on("blur",function(){checkBillingAddress($(this))
});
$("input[name='cc-zip']").on("blur change",function(){checkZip($(this))
}).on("blur",function(){checkZipCall($(this))
});
$("input[name='same_contact_info_checkbox']").on("click",function(){if($(this).is(":checked")){savedBillingInfo()
}$("div#billing-information").toggle(300)
});
$("a.billing-info-edit").on("click",function(a){a.preventDefault();
$(this).parent().parent().next().toggle("slow")
});
function setupAvailableOffers(a,c){var d=!isEmpty(sessionStorage.FFHDiscount)&&sessionStorage.FFHDiscount==="true";
var b=true;
$.ajax({async:true,type:"POST",dataType:"json",xhrFields:{withCredentials:true},headers:{isSecondaryUser:false},contentType:"application/json",url:urlDomain+"/marketing-services/signup/v3/offers/"+serviceSelected+"?quickstart="+quickStart,crossDomain:true,success:function(e){if(e.status){callMsg(false,"SetupOffersAPI",e,"Success: Setting Up Offers");
b=true
}else{callMsg(false,"SetupOffersAPI",e,"Failed: Setting Up Offers");
loadingAnimFinished("error",$("button[name='services-submit']"));
loadingAnimFinished("error",$("button[name='sign-up-modal']"));
errorRedirect(e);
b=false
}},error:function(e){callMsg(false,"SetupOffersAPI",e,"Failed: Setting Up Offers");
loadingAnimFinished("error",$("button[name='services-submit']"));
loadingAnimFinished("error",$("button[name='sign-up-modal']"));
errorRedirect(e);
b=false
},complete:function(){if(b){if(d){$.ajax({async:true,type:"POST",dataType:"json",xhrFields:{withCredentials:true},headers:{isSecondaryUser:true},contentType:"application/json",url:urlDomain+"/marketing-services/signup/v3/offers/"+serviceSelectedFFHD+"?quickstart="+quickStartFFHD,crossDomain:true,success:function(e){if(e.status){callMsg(true,"SetupOffersAPI",e,"Success: Setting Up Offers");
setTimeout(function(){addWallet(a,c)
},500)
}else{callMsg(true,"SetupOffersAPI",e,"Failed: Setting Up Offers");
loadingAnimFinished("error",$("button[name='services-submit']"));
loadingAnimFinished("error",$("button[name='sign-up-modal']"));
errorRedirect(e,true)
}},error:function(e){callMsg(true,"SetupOffersAPI",e,"Failed: Setting Up Offers");
loadingAnimFinished("error",$("button[name='services-submit']"));
loadingAnimFinished("error",$("button[name='sign-up-modal']"));
errorRedirect(e,true)
}})
}else{setTimeout(function(){addWallet(a)
},500)
}}}})
}function optimizeForm(e,g,f){var c=g?0:1;
var h=f?1:c;
var a={paymentMethods:[]};
var d={};
d.mainCard={paymentMethodType:"CC",paymentMethodPreference:1,cardType:g?typeOfCard.ffhdCard:typeOfCard.mainCard,cardHolderName:null,cardNumber:null,cardLast4:null,securityCode:null,cardExpDate:null,accountHolderName:null,bankAcctNumber:null,acctLast4:null,bankRoutingNumber:null,blockPrepaidNonReloadable:false,address:{line1:null,line2:null,zipCode:null}};
for(var b in e){if(b==="cc-name"){d.mainCard.cardHolderName=e["cc-name"][h]
}if(b==="cc-street"){d.mainCard.address.line1=e["cc-street"][h]
}if(b==="cc-zip"){d.mainCard.address.zipCode=e["cc-zip"][h]
}if(b==="creditCardNumber"){d.mainCard.cardNumber=e.creditCardNumber[h].split(" ").join("")
}if(b==="card-expiration"){d.mainCard.cardExpDate=e["card-expiration"][h].replace(/\D/g,"")
}if(b==="card-cvv"){d.mainCard.securityCode=e["card-cvv"][h]
}}a.paymentMethods.push(d.mainCard);
return a
}function addWallet(a,c){var d=!isEmpty(sessionStorage.FFHDiscount)&&sessionStorage.FFHDiscount==="true";
var b=true;
if(d){$step2.formData=a;
$step2.formDataFFHD=c
}else{$step2.formData=a
}$(".modal-content").hide("slow").html("<h2>Adding <span style='font-weight: 700'>"+(d?sessionStorage.fullName+" & "+sessionStorage.fullNameFFHD:sessionStorage.fullName)+"'s</span> Payment Information</h2>").show("slow");
$.ajax({async:true,type:"POST",dataType:"json",contentType:"application/json",xhrFields:{withCredentials:true},headers:{isSecondaryUser:false},url:urlDomain+"/marketing-services/signup/v3/wallet",crossDomain:true,data:JSON.stringify(a),success:function(e){if(e.status){callMsg(false,"addWalletAPI",e,"Success: Wallet Created/Added");
b=true
}else{callMsg(false,"addWalletAPI",e,"Invalid: Wallet Created/Added");
loadingAnimFinished("error",$("button[name='services-submit']"));
loadingAnimFinished("error",$("button[name='sign-up-modal']"));
if(d&&!isEmpty(typeof e.exception)&&(e.exception.errorCode==="MDS-338"||e.exception.errorCode==="MDS-340")){b=true
}else{b=false;
errorRedirect(e,false,b)
}}},error:function(e){callMsg(false,"addWalletAPI",e,"Invalid: Wallet Created/Added");
loadingAnimFinished("error",$("button[name='services-submit']"));
loadingAnimFinished("error",$("button[name='sign-up-modal']"));
if(d&&!isEmpty(typeof e.exception)&&(e.exception.errorCode==="MDS-338"||e.exception.errorCode==="MDS-340")){b=true
}else{b=false;
errorRedirect(e,false,b)
}},complete:function(){if(b){if(d){_satellite.track("parallel_services_button");
$.ajax({async:true,type:"POST",dataType:"json",contentType:"application/json",xhrFields:{withCredentials:true},headers:{isSecondaryUser:true},url:urlDomain+"/marketing-services/signup/v3/wallet",crossDomain:true,data:JSON.stringify(c),success:function(e){if(e.status){callMsg(true,"addWalletAPI",e,"Success: Wallet Created/Added");
_satellite.track("parallel_services_button_ffhd");
storeLogs(findLogs());
loadingAnimFinished("success",$("button[name='services-submit']"));
loadingAnimFinished("success",$("button[name='sign-up-modal']"));
setTimeout(function(){window.location.href=urlEnv+"/step-3"
},2000)
}else{callMsg(true,"addWalletAPI",e,"Invalid: Wallet Created/Added");
loadingAnimFinished("error",$("button[name='services-submit']"));
loadingAnimFinished("error",$("button[name='sign-up-modal']"));
errorRedirect(e,true)
}},error:function(e){callMsg(true,"addWalletAPI",e,"Invalid: Wallet Created/Added");
loadingAnimFinished("error",$("button[name='services-submit']"));
loadingAnimFinished("error",$("button[name='sign-up-modal']"));
errorRedirect(e,true)
}})
}else{_satellite.track("parallel_services_button");
storeLogs(findLogs());
loadingAnimFinished("success",$("button[name='services-submit']"));
loadingAnimFinished("success",$("button[name='sign-up-modal']"));
setTimeout(function(){window.location.href=urlEnv+"/step-3"
},2000)
}}}})
}function allFormFields(){if(!isEmpty(sessionStorage.getItem("FFHDiscount"))&&sessionStorage.getItem("FFHDiscount")==="true"&&!sameBillingDetails){return checkCardNumber($("input#cc_num_0"))&&checkCardNumber($("input#cc_num_0-ffhd"))&&checkExp($("input#cc_exp"))&&checkExp($("input#cc_exp-ffhd"))&&checkCVV($("input#cc_cvv"))&&checkCVV($("input#cc_cvv-ffhd"))&&checkBillingName($("input#cc-name"))&&checkBillingName($("input#cc-name-ffhd"))&&checkBillingAddress($("input#cc-street"))&&checkBillingAddress($("input#cc-street-ffhd"))&&checkZip($("input#cc-zip"))&&checkZip($("input#cc-zip-ffhd"))
}else{return checkCardNumber($("input#cc_num_0"))&&checkExp($("input#cc_exp"))&&checkCVV($("input#cc_cvv"))&&checkBillingName($("input#cc-name"))&&checkBillingAddress($("input#cc-street"))&&checkZip($("input#cc-zip"))
}}function finishedForm(){if(allFormFields()){$("section.errors").hide("slow");
return true
}$("section.errors").show("slow");
return false
}$("button[name='services-submit']").on("click",function(c){c.preventDefault();
$(this).css("pointer-events","none").attr("disabled",true);
if(timeStampCheck&&finishedForm()){$(".modal-content").html("<h2>Setting up <span style='font-weight: 700'>"+(!isEmpty(sessionStorage.FFHDiscount)&&sessionStorage.FFHDiscount==="true"?sessionStorage.fullName+" & "+sessionStorage.fullNameFFHD:sessionStorage.fullName)+"'s</span> Account</h2>");
loadingAnimStart($("button[name='services-submit']"));
loadingAnimStart($("button[name='sign-up-modal']"));
var a={},b={};
if(!isEmpty(sessionStorage.FFHDiscount)&&sessionStorage.FFHDiscount==="true"){a=optimizeForm(formSerializer("form#Signup-Form"),false,sameBillingDetails);
b=optimizeForm(formSerializer("form#Signup-Form"),true,sameBillingDetails);
setupAvailableOffers(a,b)
}else{a=optimizeForm(formSerializer("form#Signup-Form"),false,sameBillingDetails);
setupAvailableOffers(a)
}}setTimeout(function(){$("button[name='services-submit']").css("pointer-events","auto").removeAttr("disabled")
},1500);
return false
});