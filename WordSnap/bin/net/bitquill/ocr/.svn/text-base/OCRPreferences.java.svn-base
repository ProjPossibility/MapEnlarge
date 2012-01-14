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

import net.bitquill.ocr.weocr.WeOCRServerList;
import android.app.AlertDialog;
import android.app.Dialog;
import android.content.DialogInterface;
import android.os.Bundle;
import android.preference.Preference;
import android.preference.PreferenceActivity;
import android.widget.ListAdapter;

public class OCRPreferences extends PreferenceActivity 
implements Preference.OnPreferenceChangeListener, Preference.OnPreferenceClickListener {
    
    public static final String PREF_WEOCR_ENDPOINT = "weocr_endpoint_url";
    
    public static final String PREF_CONTINUOUS_MODE = "continuous_mode";
    public static final String PREF_DEBUG_DUMP = "enable_debug_dump";
    public static final String PREF_DILATE_RADIUS = "dilate_radius";
    
    public static final String PREF_EDIT_BEFORE = "edit_before";
    public static final String PREF_EXTENT_ALERT = "extent_alert";
    public static final String PREF_FOCUS_ALERT = "focus_alert";
    public static final String PREF_CONTRAST_ALERT = "contrast_alert";

    // XXX - is there a better way to avoid duplication of strings types in resource file??
    // Values must match pref_alert_on_warning_* string arrays in resources.
    public static final String[] PREF_ALERT_ON_WARNING_VALUES = { "never", "edge", "3g", "always" };
    // Order is important! Condition to show alert is: alertMode <= networkAlertLevel (both represented using these values)
    public static final int PREF_ALERT_NEVER = 0;
    public static final int PREF_ALERT_EDGE = 1;
    public static final int PREF_ALERT_3G = 2;
    public static final int PREF_ALERT_ALWAYS = 3;
    // Values must match string array resource.
    public static final String[] PREF_DILATE_RADIUS_VALUES = { "small", "medium", "large" };
    public static final int PREF_DILATE_RADIUS_SMALL = 0;
    public static final int PREF_DILATE_RADIUS_MEDIUM = 1;
    public static final int PREF_DILATE_RADIUS_LARGE = 2;
    
    private static final int ID_WEOCR_SERVERS_DIALOG = 1;

    @Override
    protected void onCreate (Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        // Load XML preferences file
        addPreferencesFromResource(R.xml.ocr_prefs);
        
        Preference p = findPreference(PREF_WEOCR_ENDPOINT);
        p.setOnPreferenceClickListener(this);
        p.setOnPreferenceChangeListener(this);        
    }
    
    @Override
    protected Dialog onCreateDialog (int id) {
        switch(id) {
        case ID_WEOCR_SERVERS_DIALOG:
            final ListAdapter serversAdapter = OCRApplication.getOCRServerList().getServerListAdapter(this);
            return new AlertDialog.Builder(this)
                .setTitle(R.string.servers_dialog_title)
                .setAdapter(serversAdapter,
                        new DialogInterface.OnClickListener() {
                            @Override
                            public void onClick(DialogInterface dialog, int which) {
                                OCRApplication.getInstance().rebindServer(((WeOCRServerList.Server)serversAdapter.getItem(which)).endpoint);
                                // FIXME FIXME FIXME !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                                OCRPreferences.this.dismissDialog(ID_WEOCR_SERVERS_DIALOG);
                            }
                        })
                .setNeutralButton(R.string.close_button, null)
                .create();
        default:
            return super.onCreateDialog(id);
        }
    }
    
    @Override
    public boolean onPreferenceChange(Preference pref, Object newValue) {
        if (PREF_WEOCR_ENDPOINT.equals(pref.getKey())) {
            //pref.setSummary((String)newValue);
            // TODO
        }
        return false;
    }

    @Override
    public boolean onPreferenceClick(Preference pref) {
        if (PREF_WEOCR_ENDPOINT.equals(pref.getKey())) {
            showDialog(ID_WEOCR_SERVERS_DIALOG);
        }
        return false;
    }
    
}
