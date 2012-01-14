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
package net.bitquill.ocr;

import net.bitquill.ocr.weocr.WeOCRClient;
import net.bitquill.ocr.weocr.WeOCRServerList;
import android.app.Application;
import android.content.SharedPreferences;
import android.content.SharedPreferences.Editor;
import android.preference.PreferenceManager;

public class OCRApplication extends Application {

    private static OCRApplication sMe;
    
    private WeOCRClient mWeOCRClient;
    private WeOCRServerList mWeOCRServerList;
    
    public OCRApplication () {
        sMe = this;
    }
    
    public static final OCRApplication getInstance () {
        return sMe;
    }
    
    public static final WeOCRClient getOCRClient () {
        return sMe.mWeOCRClient;
    }
    
    public static final WeOCRServerList getOCRServerList () {
        return sMe.mWeOCRServerList;
    }
    
    @Override
    public void onCreate() {
        super.onCreate();

        rebindServer(null);
        
        // Load WeOCR server list
        try {
            mWeOCRServerList = new WeOCRServerList(this, R.xml.weocr);
        } catch (Throwable t) {
            // TODO
        }
    }

    public void rebindServer (String endpointUrl) {
        SharedPreferences preferences = 
            PreferenceManager.getDefaultSharedPreferences(this);
        if (endpointUrl == null) {
            // Get preferred endpoint URL
            endpointUrl = preferences.getString(OCRPreferences.PREF_WEOCR_ENDPOINT,
                    getString(R.string.pref_weocr_server_default));
        } else {
            // Set preferred endpoint URL
            Editor editor = preferences.edit();
            editor.putString(OCRPreferences.PREF_WEOCR_ENDPOINT, endpointUrl);
            editor.commit();
            // XXX - check (the pref activity won't have a clue about the change??)
        }
        // Initialize new Delicious HTTP client
        mWeOCRClient = new WeOCRClient(endpointUrl);
        
        // FIXME FIXME FIXME !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    }
}
