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

import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;

import net.bitquill.ocr.image.GrayImage;
import android.graphics.Bitmap;
import android.graphics.Bitmap.CompressFormat;
import android.util.Log;

/**
 * Simple utility class to dump image data for later debugging.
 */
public class FileDumpUtil {
    
    private static final String TAG = "FileDumpUtil";
    private static final File sDumpDirectory = new File("/sdcard/net.bitquill.ocr");
    
    synchronized static public void init () {
        // Create directory, if necessary
        if (!sDumpDirectory.exists()) {
            sDumpDirectory.mkdirs();
        }
    }
        
    synchronized static public void dump (String prefix, GrayImage img) {
        FileOutputStream os = null;
        try {
            long timestamp = System.currentTimeMillis();
            File dumpFile = new File(sDumpDirectory, prefix + timestamp + ".gray");
            os = new FileOutputStream(dumpFile);
            os.write(img.getData());
        } catch (IOException ioe) {
            Log.e(TAG, "GrayImage dump failed", ioe);
        } finally {
            try {
                os.close();
            } catch (Throwable t) {
                // Ignore
            }
        }
    }
    
    synchronized static public void dump (String prefix, Bitmap img) {
        FileOutputStream os = null;
        try {
            long timestamp = System.currentTimeMillis();
            File dumpFile = new File(sDumpDirectory, prefix + timestamp + ".png");
            os = new FileOutputStream(dumpFile);
            img.compress(CompressFormat.PNG, 100, os);
        } catch (IOException ioe) {
            Log.e(TAG, "GrayImage dump failed", ioe);
        } finally {
            try {
                os.close();
            } catch (Throwable t) {
                // Ignore
            }
        }        
    }
}
