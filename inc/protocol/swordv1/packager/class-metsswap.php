<?php

namespace Excalibur\Protocol\SwordV1\Packager;

class MetsSwap {

	/**
	 * The working directory, usually /tmp, no trailing slash!
	 *
	 * @var string
	 */
	public $workingDir;

	/**
	 * The filename to save the package as
	 *
	 * @var string
	 */
	public $fileOut;

	/**
	 * The name of the metadata file
	 *
	 * @var string
	 */
	public $fileMeta = 'mets.xml';

	/**
	 * The type (e.g. ScholarlyWork)
	 *
	 * @var string
	 */
	public $type;

	/**
	 * The title of the item
	 *
	 * @var string
	 */
	public $title;

	/**
	 * The abstract of the item
	 *
	 * @var string
	 */
	public $abstract;

	/**
	 * Creators
	 *
	 * @var array
	 */
	public $creators = [];

	/**
	 * Subjects
	 *
	 * @var array
	 */
	public $subjects = [];

	/**
	 * Identifier
	 *
	 * @var string
	 */
	public $identifier;

	/**
	 * Date made available
	 *
	 * @var
	 */
	public $dateAvailable;

	/**
	 * Status
	 *
	 * @var string
	 */
	public $statusStatement;

	/**
	 * Copyright holder
	 *
	 * @var string
	 */
	public $copyrightHolder;

	/**
	 * Custodian
	 *
	 * @var string
	 */
	public $custodian;

	/**
	 * Bibliographic citation
	 *
	 * @var string
	 */
	public $citation;

	/**
	 * Language
	 *
	 * @var string
	 */
	public $language;

	/**
	 * Files
	 *
	 * @var array
	 */
	public $files = [];

	/**
	 * MIME types
	 *
	 * @var array
	 */
	public $mimetypes = [];

	/**
	 * Provenances
	 *
	 * @var array
	 */
	public $provenances = [];

	/**
	 * Rights
	 *
	 * @var array
	 */
	public $rights = [];

	/**
	 * Publisher
	 *
	 * @var string
	 */
	public $publisher;

	/**
	 * Number of files added
	 *
	 * @var int
	 */
	public $filecount = 0;

	/**
	 * @param string $working_dir
	 * @param string $sac_fileout
	 */
	public function __construct( $working_dir, $sac_fileout ) {
		$this->workingDir = untrailingslashit( $working_dir );
		$this->fileOut = $sac_fileout;
	}

	/**
	 * @param string $sac_thetype
	 */
	public function setType( $sac_thetype ) {
		$this->type = $sac_thetype;
	}

	/**
	 * @param string $sac_thetitle
	 */
	public function setTitle( $sac_thetitle ) {
		$this->title = $this->clean( $sac_thetitle );
	}

	/**
	 * @param string $sac_thetitle
	 */
	public function setAbstract( $sac_thetitle ) {
		$this->abstract = $this->clean( $sac_thetitle );
	}

	/**
	 * @param string $sac_creator
	 */
	public function addCreator( $sac_creator ) {
		$this->creators[] = $this->clean( $sac_creator );
	}

	/**
	 * @param string $sac_subject
	 */
	public function addSubject( $sac_subject ) {
		$this->subjects[] = $this->clean( $sac_subject );
	}

	/**
	 * @param string $sac_provenance
	 */
	public function addProvenance( $sac_provenance ) {
		$this->provenances[] = $this->clean( $sac_provenance );
	}

	/**
	 * @param string $sac_right
	 */
	public function addRights( $sac_right ) {
		$this->rights[] = $this->clean( $sac_right );
	}

	/**
	 * @param string $sac_theidentifier
	 */
	public function setIdentifier( $sac_theidentifier ) {
		$this->identifier = $sac_theidentifier;
	}

	/**
	 * @param string $sac_thestatus
	 */
	public function setStatusStatement( $sac_thestatus ) {
		$this->statusStatement = $sac_thestatus;
	}

	/**
	 * @param string $sac_thecopyrightholder
	 */
	public function setCopyrightHolder( $sac_thecopyrightholder ) {
		$this->copyrightHolder = $sac_thecopyrightholder;
	}

	/**
	 * @param string $sac_thecustodian
	 */
	public function setCustodian( $sac_thecustodian ) {
		$this->custodian = $this->clean( $sac_thecustodian );
	}

	/**
	 * @param string $sac_thecitation
	 */
	public function setCitation( $sac_thecitation ) {
		$this->citation = $this->clean( $sac_thecitation );
	}

	/**
	 * @param string $sac_thelanguage
	 */
	public function setLanguage( $sac_thelanguage ) {
		$this->language = $this->clean( $sac_thelanguage );
	}

	/**
	 * @param string $sac_thedta
	 */
	public function setDateAvailable( $sac_thedta ) {
		$this->dateAvailable = $sac_thedta;
	}

	/**
	 * @param string $sac_thepublisher
	 */
	public function setPublisher( $sac_thepublisher ) {
		$this->publisher = $sac_thepublisher;
	}

	/**
	 * @param string $sac_thefile
	 * @param string $sac_themimetype
	 *
	 * @throws \Exception
	 */
	public function addFile( $sac_thefile, $sac_themimetype ) {
		if ( ! file_exists( $sac_thefile ) ) {
			throw new \Exception( "File not found: $sac_thefile" );
		}
		$this->files[] = $sac_thefile;
		$this->mimetypes[] = $sac_themimetype;
		$this->filecount++;
	}

	/**
	 * @return string full path to created file
	 *
	 * @throws \Exception
	 */
	public function create() {
		// Write the metadata (mets) file
		$fh = @fopen( $this->workingDir . '/' . $this->fileMeta, 'w' ); // @codingStandardsIgnoreLine
		if ( ! $fh ) {
			throw new \Exception(
				"Error writing metadata file ({$this->workingDir}/{$this->fileMeta})"
			);
		}
		$this->writeHeader( $fh );
		$this->writeDmdSec( $fh );
		$this->writeFileGrp( $fh );
		$this->writeStructMap( $fh );
		$this->writeFooter( $fh );
		fclose( $fh );

		// Create the zipped package (force an overwrite if it already exists)
		$zip = new \ZipArchive();
		$zip->open( $this->workingDir . '/' . $this->fileOut, \ZIPARCHIVE::CREATE | \ZIPARCHIVE::OVERWRITE );
		$zip->addFile(
			$this->workingDir . '/' . $this->fileMeta,
			$this->fileMeta
		);
		for ( $i = 0; $i < $this->filecount; $i++ ) {
			$zip->addFile(
				$this->files[ $i ],
				basename( $this->files[ $i ] )
			);
		}
		$zip->close();

		return $this->workingDir . '/' . $this->fileOut;
	}

	/**
	 * @param resource $fh
	 */
	protected function writeHeader( $fh ) {
		fwrite( $fh, '<?xml version="1.0" encoding="utf-8" standalone="no" ?' . ">\n" );
		fwrite( $fh, "<mets ID=\"sort-mets_mets\" OBJID=\"sword-mets\" LABEL=\"DSpace SWORD Item\" PROFILE=\"DSpace METS SIP Profile 1.0\" xmlns=\"http://www.loc.gov/METS/\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.loc.gov/METS/ http://www.loc.gov/standards/mets/mets.xsd\">\n" );
		fwrite( $fh, "\t<metsHdr CREATEDATE=\"2008-09-04T00:00:00\">\n" );
		fwrite( $fh, "\t\t<agent ROLE=\"CUSTODIAN\" TYPE=\"ORGANIZATION\">\n" );
		if ( isset( $this->custodian ) ) {
			fwrite( $fh, "\t\t\t<name>$this->custodian</name>\n" );
		} else {
			fwrite( $fh, "\t\t\t<name>Unknown</name>\n" );
		}
		fwrite( $fh, "\t\t</agent>\n" );
		fwrite( $fh, "\t</metsHdr>\n" );
	}

	/**
	 * @param resource $fh
	 */
	protected function writeDmdSec( $fh ) {
		fwrite( $fh, "<dmdSec ID=\"sword-mets-dmd-1\" GROUPID=\"sword-mets-dmd-1_group-1\">\n" );
		fwrite( $fh, "<mdWrap LABEL=\"SWAP Metadata\" MDTYPE=\"OTHER\" OTHERMDTYPE=\"EPDCX\" MIMETYPE=\"text/xml\">\n" );
		fwrite( $fh, "<xmlData>\n" );
		fwrite( $fh, "<epdcx:descriptionSet xmlns:epdcx=\"http://purl.org/eprint/epdcx/2006-11-16/\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://purl.org/eprint/epdcx/2006-11-16/ http://purl.org/eprint/epdcx/xsd/2006-11-16/epdcx.xsd\">\n" );
		fwrite( $fh, "<epdcx:description epdcx:resourceId=\"sword-mets-epdcx-1\">\n" );
		if ( isset( $this->type ) ) {
			$this->statementVesURIValueURI(
				$fh,
				'http://purl.org/dc/elements/1.1/type',
				'http://purl.org/eprint/terms/Type',
				$this->type
			);
		}
		if ( isset( $this->title ) ) {
			$this->statement(
				$fh,
				'http://purl.org/dc/elements/1.1/title',
				$this->valueString( $this->title )
			);
		}
		if ( isset( $this->abstract ) ) {
			$this->statement(
				$fh,
				'http://purl.org/dc/terms/abstract',
				$this->valueString( $this->abstract )
			);
		}
		foreach ( $this->creators as $sac_creator ) {
			$this->statement(
				$fh,
				'http://purl.org/dc/elements/1.1/creator',
				$this->valueString( $sac_creator )
			);
		}
		foreach ( $this->subjects as $sac_subject ) {
			$this->statement(
				$fh,
				'http://purl.org/dc/elements/1.1/subject',
				$this->valueString( $sac_subject )
			);
		}
		foreach ( $this->provenances as $sac_provenance ) {
			$this->statement(
				$fh,
				'http://purl.org/dc/terms/provenance',
				$this->valueString( $sac_provenance )
			);
		}
		foreach ( $this->rights as $sac_right ) {
			$this->statement(
				$fh,
				'http://purl.org/dc/terms/rights',
				$this->valueString( $sac_right )
			);
		}
		if ( isset( $this->identifier ) ) {
			$this->statement(
				$fh,
				'http://purl.org/dc/elements/1.1/identifier',
				$this->valueString( $this->identifier )
			);
		}
		if ( isset( $this->publisher ) ) {
			$this->statement(
				$fh,
				'http://purl.org/dc/elements/1.1/publisher',
				$this->valueString( $this->publisher )
			);
		}
		fwrite(
			$fh,
			'<epdcx:statement epdcx:propertyURI="http://purl.org/eprint/terms/isExpressedAs" ' .
			"epdcx:valueRef=\"sword-mets-expr-1\" />\n"
		);
		fwrite( $fh, "</epdcx:description>\n" );

		fwrite( $fh, "<epdcx:description epdcx:resourceId=\"sword-mets-expr-1\">\n" );

		$this->statementValueURI(
			$fh,
			'http://purl.org/dc/elements/1.1/type',
			'http://purl.org/eprint/entityType/Expression'
		);

		if ( isset( $this->language ) ) {
			$this->statementVesURI(
				$fh,
				'http://purl.org/dc/elements/1.1/language',
				'http://purl.org/dc/terms/RFC3066',
				$this->valueString( $this->language )
			);
		}

		$this->statementVesURIValueURI(
			$fh,
			'http://purl.org/dc/elements/1.1/type',
			'http://purl.org/eprint/terms/Type',
			'http://purl.org/eprint/entityType/Expression'
		);

		if ( isset( $this->dateAvailable ) ) {
			$this->statement(
				$fh,
				'http://purl.org/dc/terms/available',
				$this->valueStringSesURI(
					'http://purl.org/dc/terms/W3CDTF',
					$this->dateAvailable
				)
			);
		}
		if ( isset( $this->statusStatement ) ) {
			$this->statementVesURIValueURI(
				$fh,
				'http://purl.org/eprint/terms/Status',
				'http://purl.org/eprint/terms/Status',
				$this->statusStatement
			);
		}
		if ( isset( $this->copyrightHolder ) ) {
			$this->statement(
				$fh,
				'http://purl.org/eprint/terms/copyrightHolder',
				$this->valueString( $this->copyrightHolder )
			);
		}
		if ( isset( $this->citation ) ) {
			$this->statement(
				$fh,
				'http://purl.org/eprint/terms/bibliographicCitation',
				$this->valueString( $this->citation )
			);
		}
		fwrite( $fh, "</epdcx:description>\n" );

		fwrite( $fh, "</epdcx:descriptionSet>\n" );
		fwrite( $fh, "</xmlData>\n" );
		fwrite( $fh, "</mdWrap>\n" );
		fwrite( $fh, "</dmdSec>\n" );
	}

	/**
	 * @param resource $fh
	 */
	protected function writeFileGrp( $fh ) {
		fwrite( $fh, "\t<fileSec>\n" );
		fwrite( $fh, "\t\t<fileGrp ID=\"sword-mets-fgrp-1\" USE=\"CONTENT\">\n" );
		for ( $i = 0; $i < $this->filecount; $i++ ) {
			fwrite(
				$fh,
				"\t\t\t<file GROUPID=\"sword-mets-fgid-0\" ID=\"sword-mets-file-" . $i . '" ' .
				'MIMETYPE="' . $this->mimetypes[ $i ] . "\">\n"
			);
			fwrite( $fh, "\t\t\t\t<FLocat LOCTYPE=\"URL\" xlink:href=\"" . $this->clean( basename( $this->files[ $i ] ) ) . "\" />\n" );
			fwrite( $fh, "\t\t\t</file>\n" );
		}
		fwrite( $fh, "\t\t</fileGrp>\n" );
		fwrite( $fh, "\t</fileSec>\n" );
	}

	/**
	 * @param resource $fh
	 */
	protected function writeStructMap( $fh ) {
		fwrite( $fh, "\t<structMap ID=\"sword-mets-struct-1\" LABEL=\"structure\" TYPE=\"LOGICAL\">\n" );
		fwrite( $fh, "\t\t<div ID=\"sword-mets-div-1\" DMDID=\"sword-mets-dmd-1\" TYPE=\"SWORD Object\">\n" );
		fwrite( $fh, "\t\t\t<div ID=\"sword-mets-div-2\" TYPE=\"File\">\n" );
		for ( $i = 0; $i < $this->filecount; $i++ ) {
			fwrite( $fh, "\t\t\t\t<fptr FILEID=\"sword-mets-file-" . $i . "\" />\n" );
		}
		fwrite( $fh, "\t\t\t</div>\n" );
		fwrite( $fh, "\t\t</div>\n" );
		fwrite( $fh, "\t</structMap>\n" );
	}

	/**
	 * @param resource $fh
	 */
	protected function writeFooter( $fh ) {
		fwrite( $fh, "</mets>\n" );
	}

	/**
	 * @param string $value
	 *
	 * @return string
	 */
	protected function valueString( $value ) {
		return '<epdcx:valueString>' . $value . "</epdcx:valueString>\n";
	}

	/**
	 * @param string $ses_uri
	 * @param string $value
	 *
	 * @return string
	 */
	protected function valueStringSesURI( $ses_uri, $value ) {
		return '<epdcx:valueString epdcx:sesURI="' . $ses_uri . '">' . $value . "</epdcx:valueString>\n";
	}

	/**
	 * @param resource $fh
	 * @param string $property_uri
	 * @param string $value
	 */
	protected function statement( $fh, $property_uri, $value ) {
		fwrite(
			$fh,
			'<epdcx:statement epdcx:propertyURI="' . $property_uri . "\">\n" .
			$value .
			"</epdcx:statement>\n"
		);
	}

	/**
	 * @param resource $fh
	 * @param string $property_uri
	 * @param string $value
	 */
	protected function statementValueURI( $fh, $property_uri, $value ) {
		fwrite(
			$fh,
			'<epdcx:statement epdcx:propertyURI="' . $property_uri . '" ' .
			'epdcx:valueURI="' . $value . "\" />\n"
		);
	}

	/**
	 * @param resource $fh
	 * @param string $property_uri
	 * @param string $ves_uri
	 * @param string $value
	 */
	protected function statementVesURI( $fh, $property_uri, $ves_uri, $value ) {
		fwrite(
			$fh,
			'<epdcx:statement epdcx:propertyURI="' . $property_uri . '" ' .
			'epdcx:vesURI="' . $ves_uri . "\">\n" .
			$value .
			"</epdcx:statement>\n"
		);
	}

	/**
	 * @param resource $fh
	 * @param string $property_uri
	 * @param string $ves_uri
	 * @param string $value
	 */
	protected function statementVesURIValueURI( $fh, $property_uri, $ves_uri, $value ) {
		fwrite(
			$fh,
			'<epdcx:statement epdcx:propertyURI="' . $property_uri . '" ' .
			'epdcx:vesURI="' . $ves_uri . '" ' .
			'epdcx:valueURI="' . $value . "\" />\n"
		);
	}

	/**
	 * @param string $data
	 *
	 * @return string
	 */
	protected function clean( $data ) {
		return str_replace( '&#039;', '&apos;', htmlspecialchars( $data, ENT_QUOTES ) );
	}

}
