var $name = $('#name');
var $urlPreview = $('.url-preview');
var $urlPreviewValue = $urlPreview.children('.value');
var $hiddenName = $('input[name=name]');

var lastVal = '';

handleNameChanged();

function handleNameChanged (){
    if ($name.val() != lastVal){
        var cleanVal = $name.val().replace(/\s+/g, '-').replace(/[^a-z0-9_.-]+/ig, '');
        $hiddenName.val(cleanVal);
        $urlPreview.toggleClass('hidden', cleanVal == '');
        $urlPreviewValue.text(cleanVal);
        lastVal = cleanVal;
    }
}

$name.on('keyup', handleNameChanged);
$name.on('change', function(){
    handleNameChanged();
    lastVal = $hiddenName.val();
    $name.val($hiddenName.val());
});
