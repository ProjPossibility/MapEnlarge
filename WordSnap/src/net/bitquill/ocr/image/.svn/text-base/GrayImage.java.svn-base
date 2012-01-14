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
package net.bitquill.ocr.image;

import android.graphics.Bitmap;
import android.graphics.Rect;
import android.graphics.Bitmap.Config;
import android.util.Log;

public class GrayImage extends GrayMatrix {
    
    private static final String TAG = "GrayImage";
    
    static {
        try {
            System.loadLibrary("imageproc");
        } catch (UnsatisfiedLinkError ule) {
            Log.e(TAG, "Could not load native library imageproc", ule);
        }   
    }
    
    public GrayImage (int width, int height) {
        super(width, height);
    }
    
    public GrayImage (byte[] data, int width, int height) {
        super(data, width, height);
    }
    
    public GrayImage (GrayImage other) {
        super(other);
    }
    
    final public int min (int left, int top, int right, int bottom) {
        return GrayImage.nativeMin(mData, mWidth, mHeight, left, top, right - left, bottom - top);
    }

    final public int min (Rect roi) {
        return min(roi.left, roi.top, roi.right, roi.bottom);
    }
    
    final public int min () {
        int width = mWidth, height = mHeight;
        return GrayImage.nativeMin(mData, width, height, 0, 0, width, height);
    }

    final public int max (int left, int top, int right, int bottom) {
        return GrayImage.nativeMax(mData, mWidth, mHeight, left, top, right - left, bottom - top);
    }
    
    final public int max (Rect roi) {
        return max(roi.left, roi.top, roi.right, roi.bottom);
    }
    
    final public int max () {
        int width = mWidth, height = mHeight;
        return GrayImage.nativeMax(mData, width, height, 0, 0, width, height);
    }

    final public float mean (Rect roi) {
        int left = roi.left, top = roi.top;
        return GrayImage.nativeMean(mData, mWidth, mHeight, left, top, roi.right - left, roi.bottom - top);
    }
    
    final public float mean () {
        int width = mWidth, height = mHeight;
        return GrayImage.nativeMean(mData, width, height, 0, 0, width, height);
    }
    
    final public float variance (Rect roi) {
        int left = roi.left, top = roi.top;
        return GrayImage.nativeVariance(mData, mWidth, mHeight, left, top, roi.right - left, roi.bottom - top);        
    }
    
    final public float variance () {
        int width = mWidth, height = mHeight;
        return GrayImage.nativeVariance(mData, width, height, 0, 0, width, height);
    }

    final public int[] histogram (Rect roi, int[] hist) {
        int left = roi.left, top = roi.top;
        GrayImage.nativeHistogram(mData, mWidth, mHeight, hist, left, top, roi.right - left, roi.bottom - top);
        return hist;
    }
    
    final public int[] histogram (Rect roi) {
        return histogram(roi, new int[256]);
    }

    final public int[] histogram (int[] hist) {
        int width = mWidth, height = mHeight;
        GrayImage.nativeHistogram(mData, width, height, hist, 0, 0, width, height);
        return hist;
    }
    
    final public int[] histogram () {
        return histogram(new int[256]);
    }
    
    final public GrayImage erode (StructuringElement strel, GrayImage dest) {
        if (dest.mWidth != mWidth || dest.mHeight != mHeight) {
            throw new IllegalArgumentException("Destination image size must match");
        }
        GrayImage.nativeErode(mData, dest.mData, mWidth, mHeight, 
                strel.getNumNeighbors(), 
                strel.getHorizontalOffsets(), strel.getVerticalOffsets(), 
                strel.getLinearOffsets(mWidth, mHeight), 
                strel.getMinX(), strel.getMaxX(), 
                strel.getMinY(), strel.getMaxY());
        return dest;
    }
    
    final public GrayImage erode (StructuringElement strel) {
        return erode(strel, new GrayImage(mWidth, mHeight));
    }
    
    final public GrayImage dilate (StructuringElement strel, GrayImage dest) {
        GrayImage.nativeDilate(mData, dest.mData, mWidth, mHeight, 
                strel.getNumNeighbors(), 
                strel.getHorizontalOffsets(), strel.getVerticalOffsets(), 
                strel.getLinearOffsets(mWidth, mHeight), 
                strel.getMinX(), strel.getMaxX(), 
                strel.getMinY(), strel.getMaxY());
        return dest;
    }
    
    final public GrayImage dilate (StructuringElement strel) {
        return dilate(strel, new GrayImage(mWidth, mHeight));
    }

    final public GrayImage meanFilter (int radius, GrayImage dest) {
        if (dest.mWidth != mWidth || dest.mHeight != mHeight) {
            throw new IllegalArgumentException("Destination image size must match");
        }
        GrayImage.nativeMeanFilter(mData, dest.mData, mWidth, mHeight, radius);
        return dest;
    }
    
    final public GrayImage meanFilter (int radius) {
        return meanFilter(radius, new GrayImage(mWidth, mHeight));
    }
    
    final public GrayImage adaptiveThreshold (byte hi, byte lo, int offset, GrayImage thresh, GrayImage dest) {
        int width = mWidth;
        int height = mHeight;
        if (thresh.mWidth != width || thresh.mHeight != height) {
            throw new IllegalArgumentException("Threshold image size must match");
        }
        if (dest.mWidth != width || dest.mHeight != height) {
            throw new IllegalArgumentException("Destination image size must match");            
        }
        GrayImage.nativeAdaptiveThreshold(mData, thresh.mData, dest.mData, width, height, hi, lo, offset);
        return dest;
    }
    
    final public GrayImage adaptiveThreshold (byte hi, byte lo, int offset, GrayImage thresh) {
        return adaptiveThreshold(hi, lo, offset, thresh, new GrayImage(mWidth, mHeight));
    }
    
    final public GrayImage contrastStretch (byte min, byte max, GrayImage dest) {
        int width = mWidth;
        int height = mHeight;
        if (dest.mWidth != width || dest.mHeight != height) {
            throw new IllegalArgumentException("Destination image size must match");            
        }
        GrayImage.nativeContrastStretch(mData, dest.mData, width, height, min, max);
        return dest;
    }
    
    final public GrayImage contrastStretch (byte min, byte max) {
        return contrastStretch(min, max, new GrayImage(mWidth, mHeight));
    }
    
    final public Bitmap asBitmap (int left, int top, int width, int height, int[] buf) {
        if (buf == null) {
            throw new NullPointerException("Buffer is null");
        }
        int imgWidth = mWidth;
        int imgHeight = mHeight;
        nativeGrayToARGB(mData, imgWidth, imgHeight, buf, left, top, width, height);
        Bitmap b = Bitmap.createBitmap(buf, width, height, Config.ARGB_8888);
        return b;        
    }
    
    final public Bitmap asBitmap (int left, int top, int width, int height) {
        return asBitmap(left, top, width, height, new int[width*height]);
    }
    
    final public Bitmap asBitmap (Rect roi, int[] buf) {
        return asBitmap(roi.left, roi.top, roi.width(), roi.height(), buf);
    }
    
    final public Bitmap asBitmap (Rect roi) {
        return asBitmap(roi.left, roi.top, roi.width(), roi.height());
    }
    
    final public Bitmap asBitmap (int[] buf) {
        return asBitmap(0, 0, mWidth, mHeight, buf);
    }
    
    final public Bitmap asBitmap () {
        return asBitmap(0, 0, mWidth, mHeight);
    }
     
    /**
     * Adaptive mean filter for grayscale image, using a square filter of given radius.
     * 
     * Does not perform any parameter checking, so you must ensure that:
     * (i) radius does not exceed width or height;
     * (ii) 256*(2*radius+1)^2 fits in a 32-bit integer;
     * (iii) the output byte array is sufficiently large.
     * 
     * @param in  Input grayscale image
     * @param out Output filtered grayscale image
     * @param width  Width of input image
     * @param height Height of input image
     * @param radius Radius of mean filter; diameter is 2*radius + 1
     * @return Overall image intensity mean
     */    
    native private static void nativeMeanFilter (byte[] in, byte[] out, int width, int height, int radius);
    
    native private static void nativeAdaptiveThreshold (byte[] in, byte[] thresh, byte[] out, int width, int heigth, byte hi, byte lo, int offset);
    native private static void nativeContrastStretch (byte[] in, byte[] out, int width, int height, byte min, byte max);
    
    native private static void nativeGrayToARGB (byte[] in, int imgWidth, int imgHeight, int[] out, int left, int top, int width, int height);
    
    native private static int nativeMin (byte[] in, int imgWidth, int imgHeight, int left, int top, int width, int height);
    native private static int nativeMax (byte[] in, int imgWidth, int imgHeight, int left, int top, int width, int height);
    native private static float nativeMean (byte[] in, int imgWidth, int imgHeight, int left, int top, int width, int height);
    native private static float nativeVariance (byte[] in, int imgWidth, int imgHeight, int left, int top, int width, int height);

    native private static void nativeHistogram (byte[] in, int imgWidth, int imgHeight, int[] hist, int left, int top, int width, int height);
    
    native private static void nativeErode (byte[] in, byte[] out, int width, int height, 
            int numNeighbors, int[] hOffsets, int vOffsets[], int linearOffsets[],
            int minX, int maxX, int minY, int maxY);
    native private static void nativeDilate (byte[] in, byte[] out, int width, int height, 
            int numNeighbors, int[] hOffsets, int vOffsets[], int linearOffsets[],
            int minX, int maxX, int minY, int maxY);
}