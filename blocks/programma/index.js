import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';

registerBlockType('sportlink/programma', {
	edit: Edit,
});
