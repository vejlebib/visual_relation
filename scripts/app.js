$(function() {

	var cart = [];
	var slideNo = 0;
	var slideTimeout = 10000;
	var slideTimer = 0;

	function showAvailability(id) {
		
		$("#availability").html("");
		(window.$ || window.jQuery).ajax("http://dev.vejlebib.dk/ting-visual-relation/get-availability/" + id + "?callback=?", {
			cache: true,
			dataType: "jsonp",
			success: function(data) {
				$("#availability").html(data.available ? "Er hjemme" : "<span>Er ikke hjemme</span>");
			}
		});
	}

	function resetCart() {
		
		collapseDialogs();
		$("#cart-button").hide().html("0");
		$("#cart span").html("");
		cart = [];
	}

	function collapseDialogs() {
		
		$("#cart").hide();
		$("#bottom button").removeClass("active");
		$("#details").hide();
	}

	function showNextSlide() {
		
		resetCart();
		slideNo = (slideNo == $("#bottom button").length) ? 1 : slideNo + 1;
		$("#bottom button:eq(" + (slideNo - 1) + ")").addClass("active").trigger("click");
	}

	function onObjectClick(o) {
		
		// object-id looks like a subject, so do a structural search on it
		if (o.id.indexOf("-") == -1 && o.id.indexOf(":") == -1) {
			collapseDialogs();
			relvis.open("structural", "search:" + o.id);
			return;
		}
		
		// abort if clicked before the objects properties are loaded
		if (!o.properties.id) return; 
		
		var p = o.properties;
		
		$("#cover").attr("src", p.cover ? p.cover : p.defaultCover);
		$("#title").html(o.title);
		$("#abstract").html(p.abstract ? String(p.abstract) : "");
		showAvailability(o.id);
		
		// only show circular button if related objects exists
		if (p.related) {
			$("#details-circular").show();
		}
		else {
			$("#details-circular").hide();
		}
		
		$("#details-circular").unbind("click").click(function(e) {
			collapseDialogs();
			relvis.open("circular", o.id);
		});
		
		$("#details-external").unbind("click").click(function(e) {
			collapseDialogs();
			relvis.open("external", o.id);
		});
		
		$("#details-cart").unbind("click").click(function(e) {
			for (var i = 0; i < cart.length; i++) {
				if (cart[i]["id"] == o.id) return; // abort if already in cart
			}
			var title = o.title.length > 42 ? o.title.substring(0, 40) + "..." : o.title;
			cart.push({"id": o.id, "title": title});
			$("#cart span").append("<div>" + cart[i]["title"] + "</div>");
			$("#cart-button").html(cart.length).show();
		});
		
		$("#details").show();
	}
	
	// on every click the idle timeout is reset
	$(document).click(function() {
		
		clearInterval(slideTimer);
		slideTimer = setInterval(showNextSlide, slideTimeout);
	})

	$("#bottom button").click(function() {
		
		collapseDialogs();
		$(this).addClass("active");
	});

	$("#details-close").click(function() {
		
		$("#details").hide();
	});
	
	// although never really submitting, we use the query forms submit so it can be triggered from keyboard
	$("form").submit(function() {
		
		relvis.open("structural", "search:" + $("#query").val());
		$("#query").val("");
		collapseDialogs();
		return false;
	});

	$("#cart-button").click(function() {
		
		$("#cart").toggle();
	});

	$("#email-button").click(function() {
		
		// faking emails for now
		$("#msg").show();
		setTimeout(function() { $("#msg").hide(); }, 3000);
		resetCart();
	});
	
	slideTimer = setInterval(showNextSlide, slideTimeout);

	relvis.init({
		apiUrl: "https://dev.vejlebib.dk/ting-visual-relation",
		logUrl: "http://relvis.solsort.com/_relvis_log.js",
		relatedUrl: "http://relvis.solsort.com/relvis-api",
		loadingCover: "images/wait.png",
		disablePrefetch: false,
		clickHandle: onObjectClick,
		closeHandle: function(){},
	});
	
	// override the visual browsers margins to fit top & bottom bars
	relvis.topMargin = 80;
	relvis.bottomMargin = 80;
	
	// override the visual browsers drawBackground to remove its background + close-button & adjust styling
	relvis.drawBackground = function(ctx, x, y, w, h) {
		
		if (relvis.getType() === "ext" && relvis.nodes.length >= 15) {
			
			ctx.shadowBlur = relvis.unit / 3;
			ctx.shadowColor = "#6eb5a8";
			ctx.fillStyle = "#101a1c";
			ctx.font = "700 italic " + relvis.unit * 3 + "px arial, sans-serif";
			ctx.fillText("Forfatter", x + 4 * relvis.unit, y + 4 * relvis.unit);
			var width = ctx.measureText("Anmeldelser").width;
			ctx.fillText("Anmeldelser", x + w - width - 4 * relvis.unit, y + 4 * relvis.unit);
			ctx.fillText("Emner", x + 4 * relvis.unit, y + h - 2 * relvis.unit);
			width = ctx.measureText("Struktur").width;
			ctx.fillText("Struktur", x + w - width - 4 * relvis.unit, y + h - 2 * relvis.unit);
			ctx.shadowBlur = 0;
		}
	};
	
});