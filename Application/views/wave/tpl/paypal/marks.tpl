[{capture assign=pageScript}]
    $('.paypal-mark-container').each(function(){
        if ($(this).data('funding')) {
          const fundingSource = $(this).data('funding').toLowerCase().replace('_','');
          const mark = paypal.Marks({
            fundingSource: fundingSource
          });

          // Check if the mark is eligible
          console.log(fundingSource, mark, mark.isEligible());
          if (mark.isEligible()) {
            // Render the standalone mark for that funding source
            mark.render($(this).get(0));
          }
        }
    });
[{/capture}]
[{oxscript add=$pageScript}]