<?php
namespace Manychois\Wpx\Tests\Views;

use Manychois\Wpx\View;

class MasterView extends View
{

	#region Manychois\Wpx\View Members

	/**
	 * Implements this to define how this view should be rendered.
	 *
	 * @return void
	 */
	protected function content()
	{
		$model = $this->model;
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	<title>
		<?php echo $model['title']; ?>
	</title>
	<?php $this->section('head'); ?>
</head>
<body>
	<?php $this->body(); ?>
	<?php $this->section('scripts'); ?>
</body>
</html>
<?php
	}

	#endregion
}