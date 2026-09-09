/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';
/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	Placeholder,
	PanelBody,
	SearchControl,
	Spinner,
	Button,
	Flex,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useDebouncedInput } from '@wordpress/compose';
import { useState } from '@wordpress/element';

const PER_PAGE = 10;

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @param {Object}   props               Component props.
 * @param {Object}   props.attributes    Block attributes.
 * @param {Function} props.setAttributes Setter for the block attributes.
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {
	const postId = attributes.postId;
	const postTitle = attributes.postTitle;
	const postUrl = attributes.postUrl;

	const blockProps = useBlockProps( { className: 'dmg-read-more' } );

	const [ searchInput, setSearchInput, debouncedSearch ] =
		useDebouncedInput( '' );

	const [ page, setPage ] = useState( 1 );

	const { posts, hasResolved } = useSelect(
		( select ) => {
			const query = {
				per_page: PER_PAGE,
				status: 'publish',
				_fields: [ 'id', 'title', 'link' ],
				page,
			};

			if ( /^\d+$/.test( debouncedSearch ) ) {
				query.include = [ parseInt( debouncedSearch, 10 ) ];
			} else if ( debouncedSearch ) {
				query.search = debouncedSearch;
			}

			const selectorArgs = [ 'postType', 'post', query ];

			return {
				posts: select( 'core' ).getEntityRecords( ...selectorArgs ),
				hasResolved: select( 'core' ).hasFinishedResolution(
					'getEntityRecords',
					selectorArgs
				),
			};
		},
		[ debouncedSearch, page ]
	);

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Read More link', 'dmg-read-more' ) }>
					<SearchControl
						label={ __( 'Search posts', 'dmg-read-more' ) }
						value={ searchInput }
						onChange={ ( value ) => {
							setSearchInput( value );
							setPage( 1 );
						} }
					/>

					{ ! hasResolved && <Spinner /> }

					{ hasResolved && posts?.length === 0 && (
						<p>{ __( 'No posts found', 'dmg-read-more' ) }</p>
					) }

					{ hasResolved && posts?.length > 0 && (
						<ul>
							{ posts.map( ( post ) => (
								<li key={ post.id }>
									<Button
										variant="link"
										onClick={ () =>
											setAttributes( {
												postId: post.id,
												postTitle: post.title.rendered,
												postUrl: post.link,
											} )
										}
									>
										{ post.title.rendered }
									</Button>
								</li>
							) ) }
						</ul>
					) }

					{ hasResolved && (
						<Flex justify="space-between">
							<Button
								variant="secondary"
								disabled={ page === 1 }
								onClick={ () => setPage( page - 1 ) }
							>
								{ __( 'Previous', 'dmg-read-more' ) }
							</Button>
							<Button
								variant="secondary"
								disabled={ posts?.length < PER_PAGE }
								onClick={ () => setPage( page + 1 ) }
							>
								{ __( 'Next', 'dmg-read-more' ) }
							</Button>
						</Flex>
					) }
				</PanelBody>
			</InspectorControls>
			{ postId ? (
				<p { ...blockProps }>
					<a href={ postUrl }>Read More: { postTitle }</a>
				</p>
			) : (
				<div { ...blockProps }>
					<Placeholder
						icon="admin-links"
						label={ __( 'DMG Read More', 'dmg-read-more' ) }
						instructions={ __(
							'Search for a post in the block settings sidebar to create a Read More link.',
							'dmg-read-more'
						) }
					/>
				</div>
			) }
		</>
	);
}
