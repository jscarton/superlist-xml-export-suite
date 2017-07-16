<?php
function superlist_export_xml_to_SAP($order_xml,$order_id)
{
	//check exports directory exists
	$base_dir=superlist_export_xml_check_dir();
	//write xml file to that directory
	$timestamp=date("Y_m_d_H_i_s");	
	$filename="orders-export-".$timestamp."_$order_id.xml";
	$local_filename=$base_dir."/".$filename;
	$written=file_put_contents($local_filename, $order_xml->asXML());
	if ($written!==FALSE)
	{
		//get remote filename
		$remote_file=SUPERLIST_TO_SAP_FTP_ROOT.$filename;
		// establecer una conexión básica
		$conn_id = ftp_connect(SUPERLIST_TO_SAP_FTP_SERVER);
		if ($conn_id!==FALSE)
		{
			// iniciar sesión con nombre de usuario y contraseña
			$login_result = ftp_login($conn_id, SUPERLIST_TO_SAP_FTP_USER, SUPERLIST_TO_SAP_FTP_PASS);
			// activar modo pasivo
        	ftp_pasv($conn_id, true);
			// cargar un archivo
			if (ftp_put($conn_id, $remote_file, $local_filename, FTP_ASCII)) {
				update_post_meta($order_id,'_superlist_exported_order','1'); 			
	 			return true;
			} else { 			
	 			return false;
			}
			// cerrar la conexión ftp
			ftp_close($conn_id);
		}
	}
	return false;
}

function superlist_export_xml_check_dir()
{
	$upload_dir = wp_upload_dir();
	$exports_dir = $upload_dir['basedir'].'/superlist-xml-exports-to-sap';
	if ( ! file_exists( $exports_dir ) ) {
    	wp_mkdir_p( $exports__dir );
	}
	return $exports_dir;
}