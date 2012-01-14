/**
 * Copyright 2009 Spiros Papadimitriou <spapadim@cs.cmu.edu>
 * 
 * This file is part of WordSnap OCR.
 * 
 * WordSnap is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * WordSnap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with WordSnap.  If not, see <http://www.gnu.org/licenses/>.
 */
package net.bitquill.ocr.weocr;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;

import org.apache.http.HttpResponse;
import org.apache.http.client.HttpResponseException;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.params.BasicHttpParams;
import org.apache.http.params.HttpConnectionParams;
import org.apache.http.params.HttpParams;
import org.apache.http.params.HttpProtocolParams;
import org.apache.http.protocol.HTTP;

import android.graphics.Bitmap;
import android.util.Log;

public final class WeOCRClient {
    private static final String TAG = WeOCRClient.class.getSimpleName();

    private static final int HTTP_TIMEOUT = 6000; // in msec

    private static final String USER_AGENT_STRING = "net.bitquill.ocr/0.1" +
    " (Linux; U; Android " + android.os.Build.VERSION.RELEASE + ")" +
    " Apache-HttpClient/UNAVAILABLE" +
    " spapadim@cs.cmu.edu";
    
    private String mEndpoint;
    private DefaultHttpClient mHttpClient;
    
    public WeOCRClient (String endpoint) {
        mEndpoint = endpoint;
        HttpParams params = new BasicHttpParams();
        HttpProtocolParams.setContentCharset(params, HTTP.DEFAULT_CONTENT_CHARSET);
        HttpProtocolParams.setUseExpectContinue(params, true);
        HttpProtocolParams.setUserAgent(params, USER_AGENT_STRING);
        HttpConnectionParams.setConnectionTimeout(params, HTTP_TIMEOUT);
        mHttpClient = new DefaultHttpClient(params);
    }
    
    public String doOCR (Bitmap img) throws IOException {
        Log.i(TAG, "Sending OCR request to " + mEndpoint);
        HttpPost post = new HttpPost(mEndpoint);
        post.setEntity(new WeOCRFormEntity(img));

        // Send request and obtain response
        BufferedReader r = null;
        try {
            HttpResponse resp = mHttpClient.execute(post);
            r = new BufferedReader(new InputStreamReader(resp.getEntity().getContent(), "utf-8"));
        } catch (NullPointerException npe) {
            Log.e(TAG, "Null entity?", npe);
            throw new IOException("HTTP request failed");  // TODO
        } catch (HttpResponseException re) {
            Log.e(TAG, "HTTP response exception", re);
            throw new IOException("HTTP request failed");  // TODO
        }
        
        // Parse response
        String status = r.readLine();
        if (status.length() != 0) {
            // XXX temporary begin
            for (String line = r.readLine();  line != null;  line = r.readLine()) {
                status += line;
            }
            // XXX temporary end
            throw new IOException("WeOCR failed with status: " + status);
        }
        StringBuilder sb = new StringBuilder();  // XXX just use string?
        for (String line = r.readLine();  line != null;  line = r.readLine()) {
            sb.append(line);
            sb.append('\n');
        }
        return sb.toString().trim();
    }
}
