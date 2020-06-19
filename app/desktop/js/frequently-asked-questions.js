var FrequentlyAskedQuestions = {
	initialize: function(){
		
		// hide answers by default
		$('.frequently_asked_questions .answer').hide();

		// show an answer if passed in
		if($('.frequently_asked_questions .desired_question').length != 0){
			$('.desired_question .question span.indicator').html('-');
			$('.desired_question .answer').show();
			CreditRepairSite.scrollTo($('.desired_question'));
		}

		// bind show answer on question click
		$('.frequently_asked_questions .question').click(function(){

			var jquestion = $(this);
			var janswer = $(this).next('.answer');

			janswer.slideToggle('fast', function(){
				if(janswer.is(':visible')){
					jquestion.find('span.indicator').html('-');
				}else{
					jquestion.find('span.indicator').html('+');
				}
			});
		});

	}
};

$(function() {
	FrequentlyAskedQuestions.initialize();
});