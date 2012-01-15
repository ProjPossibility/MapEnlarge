package com.ss12.mapenlarge;

import java.io.BufferedReader;
import java.io.File;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.net.URL;
import java.net.URLConnection;
import java.util.Iterator;
import java.util.Map;

import org.json.JSONException;
import org.json.JSONObject;


public class HttpHelper {	
	/**
	 * Performs an HTTP GET request on the specified url with the specified parameters.
	 * @param url - the URL to GET from
	 * @param parameters - the parameters to pass to the URL, formatted as they'd be in the URL
	 * @return - the server's response
	 * @throws JSONException 
	 * @throws NullPointerException 
	 * @throws IOException 
	 */
	public static JSONObject get(String url, String parameters) throws IOException, NullPointerException, JSONException {		
		URL u = new URL(url + "?" + parameters);
		URLConnection connection = u.openConnection();
		connection.connect();
    
		InputStream is = connection.getInputStream();

		return getResponse(new InputStreamReader(is, "UTF-8"));
	}
	
	/**
	 * Performs an HTTP POST on the specified URL, using the map values as parameters and uploading an optional file f.
	 * @param url - the URL to perform the POST to
	 * @param values - the parameters for the POST
	 * @param file_label - the label for the file (only meaningful if there is a file, otherwise this can be anything)
	 * @param f - a file to upload (optional)
	 * @throws JSONException 
	 * @throws NullPointerException 
	**/
	public static JSONObject post(String url, Map<String, String> values, String file_label, File f) throws IOException, NullPointerException, JSONException {
		URL Url = new URL(url);
		
		// create a boundary string
		String boundary = MultiPartFormOutputStream.createBoundary();
		URLConnection urlConn = MultiPartFormOutputStream.createConnection(Url);
		
		urlConn.setRequestProperty("Accept", "*/*");
		urlConn.setRequestProperty("Content-Type", 
				MultiPartFormOutputStream.getContentType(boundary));
		
		// set some other request headers...
		urlConn.setRequestProperty("Connection", "Keep-Alive");
		urlConn.setRequestProperty("Cache-Control", "no-cache");
		
		// no need to connect because getOutputStream() does it
		MultiPartFormOutputStream out = new MultiPartFormOutputStream(urlConn.getOutputStream(), boundary);
		
		// write a text field element
		Iterator<Map.Entry<String, String> > it = values.entrySet().iterator();
		
		// Load the values from the map
		while (it.hasNext()) {
			Map.Entry<String, String> pairs = it.next();
			out.writeField(pairs.getKey().toString(), pairs.getValue().toString());
		}
		
		// upload a file
		if (f != null)
			out.writeFile(file_label, "image/*", f);
		
		out.close();

		return getResponse(new InputStreamReader(urlConn.getInputStream()));
	}
	
	private static JSONObject getResponse(InputStreamReader isr) throws IOException, JSONException, NullPointerException {
		BufferedReader in = new BufferedReader(isr);
		
		JSONObject o = null;
		String buf = in.readLine();
		o = new JSONObject(buf);
		
		in.close();
		
		return o;
	}
}
