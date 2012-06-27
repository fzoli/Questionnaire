(function() {
    
    var q;
    
    function getQuestionId(src) {
        var id = src.parents('span').attr('id');
        return id.substr(2);
    }
    
    function getAnswerId(src) {
        var c = src.attr('class');
        return c != null ? c.substr(9) : null;
    }
    
//    function removeUndisplayedAnswers() {
//        $('span.qu').each(function(){
//            var isVisible = $(this).is(":visible");
//            if (!isVisible) $(this).find('input').each(function(){
//                $(this).attr('checked', false);
//            });
//        });
//    }
    
    function findKapocsQuestionIds(qid) {
        var ret = {};
        for (var i in q) {
            var kapcsok = q[i]['kapocs'];
            if (kapcsok == null) continue;
            var tmp = new Array();
            for (var j in kapcsok) {
                var kapocs = kapcsok[j];
                if (kapocs['kerdes'] == qid) {
                    tmp.push(kapocs['valasz']);
                }
            }
            if (tmp.length > 0) ret[i] = tmp;
        }
        return ret;
    }
    
    function setQuestionVisible(qid, visible) {
        var obj = $('span#qu'+qid);
//        if (visible) obj.show();
//        else obj.hide();
        if (visible) obj.removeClass('transparent');
        else obj.addClass('transparent');
    }
    
    function showHideQuestions(src) {
        if (src.attr('type') == 'input') return;
        var questionId = getQuestionId(src);
        var answerId = getAnswerId(src);
        if (answerId == null) return;
        var kapQs = findKapocsQuestionIds(questionId);
        for (var kapQId in kapQs) {
            var opts = kapQs[kapQId];
            var isIn = false;
            for (var i in opts) {
                if (opts[i] == answerId) {
                    isIn = true;
                    break;   
                }
            }
            setQuestionVisible(kapQId, isIn);
        }
    }
    
    function autoSelectExtra(src) {
        var val = src.val();
        src.parent().children().each(function(){
            var obj = $(this);
            if (obj.attr('type') != 'input') {
                obj.attr('checked', val != '');
                showHideQuestions(obj);
            }
        });
    }
    
    $(document).ready(function() {
        if (typeof(questions) != "undefined") {
            q = questions;
            $('input.extra_input').keyup(function(){
                autoSelectExtra($(this));
            });
            $('span.qu input:checked').each(function(){
                showHideQuestions($(this));
            });
            $('span.qu input').click(function(){
                showHideQuestions($(this));
            });
//            $('input.gomb').click(function(){
//                removeUndisplayedAnswers();
//            });
        }
    });
    
})();
